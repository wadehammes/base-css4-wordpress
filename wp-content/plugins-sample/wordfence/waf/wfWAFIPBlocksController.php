<?php
class wfWAFIPBlocksController
{
	const WFWAF_BLOCK_UAREFIPRANGE = 'UA/Referrer/IP Range not allowed';
	const WFWAF_BLOCK_COUNTRY = 'blocked access via country blocking';
	const WFWAF_BLOCK_COUNTRY_REDIR = 'blocked access via country blocking and redirected to URL';
	const WFWAF_BLOCK_COUNTRY_BYPASS_REDIR = 'redirected to bypass URL';
	const WFWAF_BLOCK_WFSN = 'Blocked by Wordfence Security Network';
	
	protected static $_currentController = null;

	public static function currentController() {
		if (self::$_currentController === null) {
			self::$_currentController = new wfWAFIPBlocksController();
		}
		return self::$_currentController;
	}
	
	public static function setCurrentController($currentController) {
		self::$_currentController = $currentController;
	}
	
	public static function synchronizeConfigSettings() {
		if (!class_exists('wfConfig')) { // Ensure this is only called when WordPress and the plugin are fully loaded
			return;
		}
		
		static $isSynchronizing = false;
		if ($isSynchronizing) {
			return;
		}
		$isSynchronizing = true;
		
		global $wpdb;
		$db = new wfDB();
		
		// Pattern Blocks
		$r1 = $db->querySelect("SELECT id, blockType, blockString FROM {$wpdb->base_prefix}wfBlocksAdv");
		$patternBlocks = array();
		foreach ($r1 as $blockRec) {
			if ($blockRec['blockType'] == 'IU') {
				$bDat = explode('|', $blockRec['blockString']);
				$ipRange = isset($bDat[0]) ? $bDat[0] : '';
				$uaPattern = isset($bDat[1]) ? $bDat[1] : '';
				$refPattern = isset($bDat[2]) ? $bDat[2] : '';
				$hostnamePattern = isset($bDat[3]) ? $bDat[3] : '';
				
				$patternBlocks[] = array('id' => $blockRec['id'], 'ipRange' => $ipRange, 'hostnamePattern' => $hostnamePattern, 'uaPattern' => $uaPattern, 'refPattern' => $refPattern);
			}
		}
		
		// Country Blocks
		$wfLog = new wfLog(wfConfig::get('apiKey'), wfUtils::getWPVersion());
		$cblCookie = $wfLog->getCBLCookieVal(); //Ensure we have the bypass cookie option set
		
		$countryBlocks = array();
		$countryBlocks['action'] = wfConfig::get('cbl_action', false);
		$countryBlocks['loggedInBlocked'] = wfConfig::get('cbl_loggedInBlocked', false);
		$countryBlocks['loginFormBlocked'] = wfConfig::get('cbl_loginFormBlocked', false);
		$countryBlocks['restOfSiteBlocked'] = wfConfig::get('cbl_restOfSiteBlocked', false);
		$countryBlocks['bypassRedirURL'] = wfConfig::get('cbl_bypassRedirURL', '');
		$countryBlocks['bypassRedirDest'] = wfConfig::get('cbl_bypassRedirDest', '');
		$countryBlocks['bypassViewURL'] = wfConfig::get('cbl_bypassViewURL', '');
		$countryBlocks['redirURL'] = wfConfig::get('cbl_redirURL', '');
		$countryBlocks['countries'] = explode(',', wfConfig::get('cbl_countries', ''));
		$countryBlocks['cookieVal'] = $cblCookie;
		
		//Other Blocks
		$otherBlocks = array('blockedTime' => wfConfig::get('blockedTime', 0));
		$otherBlockEntries = $db->querySelect("SELECT IP, blockedTime, reason, permanent, wfsn FROM {$wpdb->base_prefix}wfBlocks WHERE permanent = 1 OR (blockedTime + %d > unix_timestamp())", $otherBlocks['blockedTime']);
		$otherBlocks['blocks'] = (is_array($otherBlockEntries) ? $otherBlockEntries : array());
		foreach ($otherBlocks['blocks'] as &$b) {
			$b['IP'] = base64_encode($b['IP']);
		}
		
		// Save it
		try {
			$patternBlocksJSON = wfWAFUtils::json_encode($patternBlocks);
			wfWAF::getInstance()->getStorageEngine()->setConfig('patternBlocks', $patternBlocksJSON);
			$countryBlocksJSON = wfWAFUtils::json_encode($countryBlocks);
			wfWAF::getInstance()->getStorageEngine()->setConfig('countryBlocks', $countryBlocksJSON);
			$otherBlocksJSON = wfWAFUtils::json_encode($otherBlocks);
			wfWAF::getInstance()->getStorageEngine()->setConfig('otherBlocks', $otherBlocksJSON);
			
			wfWAF::getInstance()->getStorageEngine()->setConfig('advancedBlockingEnabled', wfConfig::get('firewallEnabled'));
			wfWAF::getInstance()->getStorageEngine()->setConfig('disableWAFIPBlocking', wfConfig::get('disableWAFIPBlocking'));
		}
		catch (Exception $e) {
			// Do nothing
		}
		$isSynchronizing = false;
	}
	
	/**
	 * @param wfWAFRequest $request
	 * @return bool|string If not blocked, returns false. Otherwise a string of the reason it was blocked or true. 
	 */
	public function shouldBlockRequest($request) {
		// Checking the user whitelist is done before reaching this call
		
		$ip = $request->getIP();
		
		//Check the system whitelist
		if ($this->checkForWhitelisted($ip)) {
			return false;
		}
		
		//Let the plugin handle these
		$wfFunc = $request->getQueryString('_wfsf');
		if ($wfFunc == 'unlockEmail' || $wfFunc == 'unlockAccess') { // Can't check validity here, let it pass through to plugin level where it can
			return false;
		}
		
		$logHuman = $request->getQueryString('wordfence_logHuman');
		if ($logHuman !== null) {
			return false;
		}
		
		//Start block checks
		$ipNum = wfWAFUtils::inet_pton($ip);
		$hostname = null;
		$ua = $request->getHeaders('User-Agent'); if ($ua === null) { $ua = ''; }
		$referer = $request->getHeaders('Referer'); if ($referer === null) { $referer = ''; }
		
		$isPaid = false;
		try {
			$isPaid = wfWAF::getInstance()->getStorageEngine()->getConfig('isPaid');
			$pluginABSPATH = wfWAF::getInstance()->getStorageEngine()->getConfig('pluginABSPATH');
			
			$patternBlocksJSON = wfWAF::getInstance()->getStorageEngine()->getConfig('patternBlocks');
			$countryBlocksJSON = wfWAF::getInstance()->getStorageEngine()->getConfig('countryBlocks');
			$otherBlocksJSON = wfWAF::getInstance()->getStorageEngine()->getConfig('otherBlocks');
		}
		catch (Exception $e) {
			// Do nothing
		}
		
		if (isset($_SERVER['SCRIPT_FILENAME']) && (strpos($_SERVER['SCRIPT_FILENAME'], $pluginABSPATH . "wp-admin/") === 0 || strpos($_SERVER['SCRIPT_FILENAME'], $pluginABSPATH . "wp-content/") === 0 || strpos($_SERVER['SCRIPT_FILENAME'], $pluginABSPATH . "wp-includes/") === 0)) {
			return false; //Rely on WordPress's own access control and blocking at the plugin level
		}
		
		// Pattern Blocks from the Advanced Blocking page (IP Range, UA, Referer)
		$patternBlocks = @wfWAFUtils::json_decode($patternBlocksJSON, true);
		if (is_array($patternBlocks)) {
			// Instead of a long block of if/else statements, using bitshifting to generate an expected value and a found value
			$ipRangeOffset = 1;
			$uaPatternOffset = 2;
			$refPatternOffset = 3;
			
			foreach ($patternBlocks as $b) {
				$expectedBits = 0;
				$foundBits = 0;
				
				if (!empty($b['ipRange'])) {
					$expectedBits |= (1 << $ipRangeOffset);
					list($start_range, $end_range) = explode('-', $b['ipRange']);
					if (preg_match('/[\.:]/', $start_range)) {
						$start_range = wfWAFUtils::inet_pton($start_range);
						$end_range = wfWAFUtils::inet_pton($end_range);
					} else {
						$start_range = wfWAFUtils::inet_pton(long2ip($start_range));
						$end_range = wfWAFUtils::inet_pton(long2ip($end_range));
					}
					
					if (strcmp($ipNum, $start_range) >= 0 && strcmp($ipNum, $end_range) <= 0) {
						$foundBits |= (1 << $ipRangeOffset);
					}
				}
				
				if (!empty($b['hostnamePattern'])) {
					$expectedBits |= (1 << $ipRangeOffset);
					if ($hostname === null) {
						$hostname = wfWAFUtils::reverseLookup($ip);
					}
					if (preg_match(wfWAFUtils::patternToRegex($b['hostnamePattern']), $hostname)) {
						$foundBits |= (1 << $ipRangeOffset);
					}
				}
				
				if (!empty($b['uaPattern'])) {
					$expectedBits |= (1 << $uaPatternOffset);
					if (wfWAFUtils::isUABlocked($b['uaPattern'], $ua)) {
						$foundBits |= (1 << $uaPatternOffset);
					}
				}
				
				if (!empty($b['refPattern'])) {
					$expectedBits |= (1 << $refPatternOffset);
					if (wfWAFUtils::isRefererBlocked($b['refPattern'], $referer)) {
						$foundBits |= (1 << $refPatternOffset);
					}
				}
				
				if ($foundBits === $expectedBits && $expectedBits > 0) {
					return array('action' => self::WFWAF_BLOCK_UAREFIPRANGE, 'id' => $b['id']);
				}
			}
		}
		// End Pattern Blocks
		
		// Country Blocking
		if ($isPaid) {
			$countryBlocks = @wfWAFUtils::json_decode($countryBlocksJSON, true);
			if (is_array($countryBlocks)) {
				$blockedCountries = $countryBlocks['countries'];
				$bareRequestURI = wfWAFUtils::extractBareURI($request->getURI());
				$bareBypassRedirURI = wfWAFUtils::extractBareURI($countryBlocks['bypassRedirURL']);
				$skipCountryBlocking = false;
				
				if ($bareBypassRedirURI && $bareRequestURI == $bareBypassRedirURI) { // Run this before country blocking because even if the user isn't blocked we need to set the bypass cookie so they can bypass future blocks.
					if ($countryBlocks['bypassRedirDest']) {
						setcookie('wfCBLBypass', $countryBlocks['cookieVal'], time() + (86400 * 365), '/', null, null, true);
						return array('action' => self::WFWAF_BLOCK_COUNTRY_BYPASS_REDIR);
					}
				}
				
				$bareBypassViewURI = wfWAFUtils::extractBareURI($countryBlocks['bypassViewURL']);
				if ($bareBypassViewURI && $bareBypassViewURI == $bareRequestURI) {
					setcookie('wfCBLBypass', $countryBlocks['cookieVal'], time() + (86400 * 365), '/', null, null, true);
					$skipCountryBlocking = true;
				}
				
				$bypassCookieSet = false;
				$bypassCookie = $request->getCookies('wfCBLBypass');
				if (isset($bypassCookie) && $bypassCookie == $countryBlocks['cookieVal']) {
					$bypassCookieSet = true;
				}
				
				if (!$skipCountryBlocking && $blockedCountries && !$bypassCookieSet) {
					$isAuthRequest = (strpos($bareRequestURI, '/wp-login.php') !== false);
					$isXMLRPC = (strpos($bareRequestURI, '/xmlrpc.php') !== false);
					$isUserLoggedIn = wfWAF::getInstance()->parseAuthCookie() !== false;
					
					// If everything is checked, make sure this always runs.
					if ($countryBlocks['loggedInBlocked'] && $countryBlocks['loginFormBlocked'] && $countryBlocks['restOfSiteBlocked']) {
						if ($blocked = $this->checkForBlockedCountry($countryBlocks, $ip, $bareRequestURI)) { return $blocked; }
					}
					// Block logged in users.
					if ($countryBlocks['loggedInBlocked'] && $isUserLoggedIn) {
						if ($blocked = $this->checkForBlockedCountry($countryBlocks, $ip, $bareRequestURI)) { return $blocked; }
					}
					// Block the login form itself and any attempt to authenticate.
					if ($countryBlocks['loginFormBlocked'] && $isAuthRequest) {
						if ($blocked = $this->checkForBlockedCountry($countryBlocks, $ip, $bareRequestURI)) { return $blocked; }
					}
					// Block requests that aren't to the login page, xmlrpc.php, or a user already logged in.
					if ($countryBlocks['restOfSiteBlocked'] && !$isAuthRequest && !$isXMLRPC && !$isUserLoggedIn) {
						if ($blocked = $this->checkForBlockedCountry($countryBlocks, $ip, $bareRequestURI)) { return $blocked; }
					}
					// XMLRPC is inaccesible when public portion of the site and auth is disabled.
					if ($countryBlocks['loginFormBlocked'] && $countryBlocks['restOfSiteBlocked'] && $isXMLRPC) {
						if ($blocked = $this->checkForBlockedCountry($countryBlocks, $ip, $bareRequestURI)) { return $blocked; }
					}
					
					// Any bypasses and other block possibilities will be checked at the plugin level once WordPress loads
				}
			}
		}
		// End Country Blocking
		
		// Other Blocks
		$otherBlocks = @wfWAFUtils::json_decode($otherBlocksJSON, true);
		if (is_array($otherBlocks)) {
			$blockedTime = $otherBlocks['blockedTime'];
			$blocks = $otherBlocks['blocks'];
			$bareRequestURI = wfWAFUtils::extractBareURI($request->getURI());
			$isAuthRequest = (strpos($bareRequestURI, '/wp-login.php') !== false);
			foreach ($blocks as $b) {
				if (!$b['permanent'] && ($b['blockedTime'] + $blockedTime) < time()) {
					continue;
				}
				
				if (base64_decode($b['IP']) != $ipNum) {
					continue;
				}
				
				if ($isAuthRequest) {
					return array('action' => self::WFWAF_BLOCK_WFSN);
				}
				
				return array('action' => (empty($b['reason']) ? '' : $b['reason']));
			}
		}
		// End Other Blocks
		
		return false;
	}
	
	public function countryRedirURL($countryBlocks = null) {
		if (!isset($countryBlocks)) {
			try {
				$countryBlocksJSON = wfWAF::getInstance()->getStorageEngine()->getConfig('countryBlocks');
			}
			catch (Exception $e) {
				return false;
			}
		}
		
		$countryBlocks = @wfWAFUtils::json_decode($countryBlocksJSON, true);
		if (is_array($countryBlocks)) {
			if ($countryBlocks['action'] == 'redir') {
				return $countryBlocks['redirURL'];
			}
		}
		return false;
	}
	
	public function countryBypassRedirURL($countryBlocks = null) {
		if (!isset($countryBlocks)) {
			try {
				$countryBlocksJSON = wfWAF::getInstance()->getStorageEngine()->getConfig('countryBlocks');
			}
			catch (Exception $e) {
				return false;
			}
		}
		
		$countryBlocks = @wfWAFUtils::json_decode($countryBlocksJSON, true);
		if (is_array($countryBlocks)) {
			return $countryBlocks['bypassRedirDest'];
		}
		return false;
	}
	
	protected function checkForBlockedCountry($countryBlock, $ip, $bareRequestURI) {
		try {
			$homeURL = wfWAF::getInstance()->getStorageEngine()->getConfig('homeURL');
		}
		catch (Exception $e) {
			//Do nothing
		}
		
		$bareRequestURI = rtrim($bareRequestURI, '/\\');
		if ($country = $this->ip2Country($ip)) {
			foreach ($countryBlock['countries'] as $blocked) {
				if (strtoupper($blocked) == strtoupper($country)) {
					if ($countryBlock['action'] == 'redir') {
						$redirURL = $countryBlock['redirURL'];
						$eRedirHost = wfWAFUtils::extractHostname($redirURL);
						$isExternalRedir = false;
						if ($eRedirHost && $homeURL && $eRedirHost != wfWAFUtils::extractHostname($homeURL)) {
							$isExternalRedir = true;
						}
						
						if ((!$isExternalRedir) && rtrim(wfWAFUtils::extractBareURI($redirURL), '/\\') == $bareRequestURI){ //Is this the URI we want to redirect to, then don't block it
							//Do nothing
						}
						else {
							return array('action' => self::WFWAF_BLOCK_COUNTRY_REDIR);
						}
					}
					else {
						return array('action' => self::WFWAF_BLOCK_COUNTRY);
					}
				}
			}
		}
		
		return false;
	}
	
	protected function checkForWhitelisted($ip) {
		$wordfenceLib = realpath(dirname(__FILE__) . '/../lib');
		include($wordfenceLib . '/wfIPWhitelist.php'); // defines $wfIPWhitelist
		foreach ($wfIPWhitelist as $group) {
			foreach ($group as $subnet) {
				if ($subnet instanceof wfWAFUserIPRange) { //Not currently reached
					if ($subnet->isIPInRange($ip)) {
						return true;
					}
				} elseif (wfWAFUtils::subnetContainsIP($subnet, $ip)) {
					return true;
				}
			}
		}
		return false;
	}
	
	protected function ip2Country($ip){
		$wordfenceLib = realpath(dirname(__FILE__) . '/../lib');
		if (!(function_exists('geoip_open') && function_exists('geoip_country_code_by_addr') && function_exists('geoip_country_code_by_addr_v6'))) {
			require_once(dirname(__FILE__) . '/wfWAFGeoIP.php');
		}
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
			$gi = geoip_open($wordfenceLib . "/GeoIPv6.dat", GEOIP_STANDARD);
			$country = geoip_country_code_by_addr_v6($gi, $ip);
		} else {
			$gi = geoip_open($wordfenceLib . "/GeoIP.dat", GEOIP_STANDARD);
			$country = geoip_country_code_by_addr($gi, $ip);
		}
		geoip_close($gi);
		return $country ? $country : '';
	}
}