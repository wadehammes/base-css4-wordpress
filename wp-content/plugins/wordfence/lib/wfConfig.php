<?php
class wfConfig {
	public static $diskCache = array();
	private static $diskCacheDisabled = false; //enables if we detect a write fail so we don't keep calling stat()
	private static $cacheDisableCheckDone = false;
	private static $table = false;
	private static $cache = array();
	private static $DB = false;
	private static $tmpFileHeader = "<?php\n/* Wordfence temporary file security header */\necho \"Nothing to see here!\\n\"; exit(0);\n?>";
	private static $tmpDirCache = false;
	public static $defaultConfig = array(
		"checkboxes" => array(
			"alertOn_critical" => true,
			"alertOn_update" => false,
			"alertOn_warnings" => true,
			"alertOn_throttle" => false,
			"alertOn_block" => true,
			"alertOn_loginLockout" => true,
			"alertOn_lostPasswdForm" => true,
			"alertOn_adminLogin" => true,
			"alertOn_nonAdminLogin" => false,
			"liveTrafficEnabled" => true,
			"scansEnabled_checkReadableConfig" => true,
			"advancedCommentScanning" => false,
			"checkSpamIP" => false,
			"spamvertizeCheck" => false,
			"liveTraf_ignorePublishers" => true,
			//"perfLoggingEnabled" => false,
			"scheduledScansEnabled" => true,
			"scansEnabled_public" => false,
			"scansEnabled_heartbleed" => true,
			"scansEnabled_core" => true,
			"scansEnabled_themes" => false,
			"scansEnabled_plugins" => false,
			"scansEnabled_malware" => true,
			"scansEnabled_fileContents" => true,
			"scansEnabled_posts" => true,
			"scansEnabled_comments" => true,
			"scansEnabled_passwds" => true,
			"scansEnabled_diskSpace" => true,
			"scansEnabled_options" => true,
			"scansEnabled_wpscan_fullPathDisclosure" => true,
			"scansEnabled_wpscan_directoryListingEnabled" => true,
			"scansEnabled_dns" => true,
			"scansEnabled_scanImages" => false,
			"scansEnabled_highSense" => false,
			"scansEnabled_oldVersions" => true,
			"scansEnabled_suspiciousAdminUsers" => true,
			"firewallEnabled" => true,
			"blockFakeBots" => false,
			"autoBlockScanners" => true,
			"loginSecurityEnabled" => true,
			"loginSec_lockInvalidUsers" => false,
			"loginSec_maskLoginErrors" => true,
			"loginSec_blockAdminReg" => true,
			"loginSec_disableAuthorScan" => true,
			"loginSec_disableOEmbedAuthor" => false,
			"other_hideWPVersion" => true,
			"other_noAnonMemberComments" => true,
			"other_blockBadPOST" => false,
			"other_scanComments" => true,
			"other_pwStrengthOnUpdate" => true,
			"other_WFNet" => true,
			"other_scanOutside" => false,
			"deleteTablesOnDeact" => false,
			"autoUpdate" => false,
			"disableCookies" => false,
			"startScansRemotely" => false,
			"disableConfigCaching" => false,
			"addCacheComment" => false,
			"disableCodeExecutionUploads" => false,
			"allowHTTPSCaching" => false,
			"debugOn" => false,
			'email_summary_enabled' => true,
			'email_summary_dashboard_widget_enabled' => true,
			'ssl_verify' => true,
			'ajaxWatcherDisabled_front' => false,
			'ajaxWatcherDisabled_admin' => false,
		),
		"otherParams" => array(
			"scan_include_extra" => "",
			// 'securityLevel' => '2',
			"alertEmails" => "", "liveTraf_ignoreUsers" => "", "liveTraf_ignoreIPs" => "", "liveTraf_ignoreUA" => "",  "apiKey" => "", "maxMem" => '256', 'scan_exclude' => '', 'whitelisted' => '', 'bannedURLs' => '', 'maxExecutionTime' => '', 'howGetIPs' => '', 'actUpdateInterval' => '', 'alert_maxHourly' => 0, 'loginSec_userBlacklist' => '',
			'liveTraf_maxRows' => 2000,
			"neverBlockBG" => "neverBlockVerified",
			"loginSec_countFailMins" => "240",
			"loginSec_lockoutMins" => "240",
			'loginSec_strongPasswds' => 'pubs',
			'loginSec_maxFailures' => "20",
			'loginSec_maxForgotPasswd' => "20",
			'maxGlobalRequests' => "DISABLED",
			'maxGlobalRequests_action' => "throttle",
			'maxRequestsCrawlers' => "DISABLED",
			'maxRequestsCrawlers_action' => "throttle",
			'maxRequestsHumans' => "DISABLED",
			'maxRequestsHumans_action' => "throttle",
			'max404Crawlers' => "DISABLED",
			'max404Crawlers_action' => "throttle",
			'max404Humans' => "DISABLED",
			'max404Humans_action' => "throttle",
			'maxScanHits' => "DISABLED",
			'maxScanHits_action' => "throttle",
			'blockedTime' => "300",
			'email_summary_interval' => 'biweekly',
			'email_summary_excluded_directories' => 'wp-content/cache,wp-content/wfcache,wp-content/plugins/wordfence/tmp',
			'allowed404s' => "/favicon.ico\n/apple-touch-icon*.png\n/*@2x.png",
		)
	);
	public static function setDefaults(){
		foreach(self::$defaultConfig['checkboxes'] as $key => $val){
			if(self::get($key) === false){
				self::set($key, $val ? '1' : '0');
			}
		}
		foreach(self::$defaultConfig['otherParams'] as $key => $val){
			if(self::get($key) === false){
				self::set($key, $val);
			}
		}
		self::set('encKey', substr(wfUtils::bigRandomHex(),0 ,16) );
		if(self::get('maxMem', false) === false ){
			self::set('maxMem', '256');
		}
		if(self::get('other_scanOutside', false) === false){
			self::set('other_scanOutside', 0);
		}

		if (self::get('email_summary_enabled')) {
			wfActivityReport::scheduleCronJob();
		} else {
			wfActivityReport::disableCronJob();
		}
	}
	public static function getExportableOptionsKeys(){
		$ret = array();
		foreach(self::$defaultConfig['checkboxes'] as $key => $val){
			$ret[] = $key;
		}
		foreach(self::$defaultConfig['otherParams'] as $key => $val){
			if($key != 'apiKey'){
				$ret[] = $key;
			}
		}
		foreach(array('cbl_action', 'cbl_countries', 'cbl_redirURL', 'cbl_loggedInBlocked', 'cbl_loginFormBlocked', 'cbl_restOfSiteBlocked', 'cbl_bypassRedirURL', 'cbl_bypassRedirDest', 'cbl_bypassViewURL') as $key){
			$ret[] = $key;
		}
		return $ret;
	}
	public static function parseOptions(){
		$ret = array();
		foreach(self::$defaultConfig['checkboxes'] as $key => $val){ //value is not used. We just need the keys for validation
			$ret[$key] = isset($_POST[$key]) ? '1' : '0';
		}
		foreach(self::$defaultConfig['otherParams'] as $key => $val){
			if(isset($_POST[$key])){
				$ret[$key] = stripslashes($_POST[$key]);
			} else {
				error_log("Missing options param \"$key\" when parsing parameters.");
			}
		}
		/* for debugging only:
		foreach($_POST as $key => $val){
			if($key != 'action' && $key != 'nonce' && (! array_key_exists($key, self::$checkboxes)) && (! array_key_exists($key, self::$otherParams)) ){
				error_log("Unrecognized option: $key");
			}
		}
		*/
		return $ret;
	}
	public static function setArray($arr){
		foreach($arr as $key => $val){
			self::set($key, $val);
		}
	}
	public static function clearCache(){
		self::$cache = array();
	}
	public static function getHTML($key){
		return esc_html(self::get($key));
	}
	public static function inc($key){
		$val = self::get($key, false);
		if(! $val){
			$val = 0;
		}
		self::set($key, $val + 1);
	}
	public static function set($key, $val){
		if($key == 'disableConfigCaching'){
			self::getDB()->queryWrite("insert into " . self::table() . " (name, val) values ('%s', '%s') ON DUPLICATE KEY UPDATE val='%s'", $key, $val, $val);
			return;
		}
	
		if(is_array($val)){
			$msg = "wfConfig::set() got an array as second param with key: $key and value: " . var_export($val, true);
			wordfence::status(1, 'error', $msg);
			return;
		}

		if (($key == 'apiKey' || $key == 'isPaid') && wfWAF::getInstance() && !WFWAF_SUBDIRECTORY_INSTALL) {
			try {
				wfWAF::getInstance()->getStorageEngine()->setConfig($key, $val);
			} catch (wfWAFStorageFileException $e) {
				error_log($e->getMessage());
			}
		}

		self::getDB()->queryWrite("insert into " . self::table() . " (name, val) values ('%s', '%s') ON DUPLICATE KEY UPDATE val='%s'", $key, $val, $val);
		self::$cache[$key] = $val;
		self::clearDiskCache();
	}
	private static function getCacheFile(){
		return WORDFENCE_PATH . 'tmp/configCache.php';
	}
	public static function clearDiskCache(){
		//When we write to the cache we just trash the whole cache on the first write. Second write won't get called because we've disabled the cache.
		// Neither will anything be loaded from the cache for the rest of this request and it also won't be updated.
		// On the next request presumably we won't be doing a set() and so the cache will be populated again and continue to be used 
		// for each request as long as set() isn't called which would start the whole process over again.
		if(! self::$diskCacheDisabled){ //We haven't had a write error to cache (so the cache is working) and clearDiskCache has not been called already
			$cacheFile = self::getCacheFile();
			@unlink($cacheFile);
			wfConfig::$diskCache = array();
		}
		self::$diskCacheDisabled = true;
	}
	public static function get($key, $default = false){
		if($key == 'disableConfigCaching'){
			$val = self::getDB()->querySingle("select val from " . self::table() . " where name='%s'", $key);
			return $val;
		}

		if(! self::$cacheDisableCheckDone){
			self::$cacheDisableCheckDone = true;
			$cachingDisabledSetting = self::getDB()->querySingle("select val from " . self::table() . " where name='%s'", 'disableConfigCaching');
			if($cachingDisabledSetting == '1'){
				self::$diskCacheDisabled = true;
			}
		}

		if(!array_key_exists($key, self::$cache)){ 
			$val = self::loadFromDiskCache($key);
			//$val = self::getDB()->querySingle("select val from " . self::table() . " where name='%s'", $key);
			self::$cache[$key] = $val;
		}
		$val = self::$cache[$key];
		return $val !== null ? $val : $default;
	}
	public static function loadFromDiskCache($key){
		if(! self::$diskCacheDisabled){
			if(isset(wfConfig::$diskCache[$key])){
				return wfConfig::$diskCache[$key];
			}

			$cacheFile = self::getCacheFile();
			if(is_file($cacheFile)){
				//require($cacheFile); //will only require the file on first parse through this code. But we dynamically update the var and update the file with each get
				try {
					$cont = @file_get_contents($cacheFile);
					if(strpos($cont, '<?php') === 0){ //"<?php die() XX"
						$cont = substr($cont, strlen(self::$tmpFileHeader));
						wfConfig::$diskCache = @unserialize($cont);
						if(isset(wfConfig::$diskCache) && is_array(wfConfig::$diskCache) && isset(wfConfig::$diskCache[$key])){
							return wfConfig::$diskCache[$key];
						}
					} //Else don't return a cached value because this is an old file without the php header so we're going to rewrite it. 
				} catch(Exception $err){ } //file_get or unserialize may fail, so just fail quietly.
			}
		}
		$val = self::getDB()->querySingle("select val from " . self::table() . " where name='%s'", $key);
		if(self::$diskCacheDisabled){
			return $val; 
		}
		wfConfig::$diskCache[$key] = isset($val) ? $val : '';
		try {
			$bytesWritten = @file_put_contents($cacheFile, self::$tmpFileHeader . serialize(wfConfig::$diskCache), LOCK_EX);
		} catch(Exception $err2){}
		if(! $bytesWritten){
			self::$diskCacheDisabled = true;
		}
		return $val;
	}
	
	private static function canCompressValue() {
		if (!function_exists('gzencode') || !function_exists('gzdecode')) {
			return false;
		}
		$disabled = explode(',', ini_get('disable_functions'));
		if (in_array('gzencode', $disabled) || in_array('gzdecode', $disabled)) {
			return false;
		}
		return true;
	}
	
	private static function isCompressedValue($data) {
		//Based on http://www.ietf.org/rfc/rfc1952.txt
		if (strlen($data) < 2) {
			return false;
		}
		
		$magicBytes = substr($data, 0, 2);
		if ($magicBytes !== (chr(0x1f) . chr(0x8b))) {
			return false;
		}
		
		//Small chance of false positives here -- can check the header CRC if it turns out it's needed
		return true;
	}
	
	private static function ser_chunked_key($key) {
		return 'wordfence_chunked_' . $key . '_';
	}
	
	public static function get_ser($key, $default) {
		//Check for a chunked value first
		$chunkedValueKey = self::ser_chunked_key($key);
		$header = self::getDB()->querySingle("select val from " . self::table() . " where name=%s", $chunkedValueKey . 'header');
		if ($header) {
			$header = unserialize($header);
			$count = $header['count'];
			$path = tempnam(sys_get_temp_dir(), $key); //Writing to a file like this saves some of PHP's in-memory copying when just appending each chunk to a string
			$fh = fopen($path, 'r+');
			$length = 0;
			for ($i = 0; $i < $count; $i++) {
				$chunk = self::getDB()->querySingle("select val from " . self::table() . " where name=%s", $chunkedValueKey . $i);
				self::getDB()->flush(); //clear cache
				if (!$chunk) {
					wordfence::status(2, 'error', "Error reassembling value for {$key}");
					return $default;
				}
				fwrite($fh, $chunk);
				$length += strlen($chunk);
				unset($chunk);
			}
			
			fseek($fh, 0);
			$serialized = fread($fh, $length);
			fclose($fh);
			unlink($path);
			
			if (self::canCompressValue() && self::isCompressedValue($serialized)) {
				$inflated = @gzdecode($serialized);
				if ($inflated !== false) {
					unset($serialized);
					return unserialize($inflated);
				}
			}
			return unserialize($serialized);
		}
		else {
			$serialized = self::getDB()->querySingle("select val from " . self::table() . " where name=%s", $key);
			self::getDB()->flush(); //clear cache
			if ($serialized) {
				if (self::canCompressValue() && self::isCompressedValue($serialized)) {
					$inflated = @gzdecode($serialized);
					if ($inflated !== false) {
						unset($serialized);
						return unserialize($inflated);
					}
				}
				return unserialize($serialized);
			}
		}
		
		return $default;
	}
	
	public static function set_ser($key, $val, $allowCompression = false) {
		/*
		 * Because of the small default value for `max_allowed_packet` and `max_long_data_size`, we're stuck splitting
		 * large values into multiple chunks. To minimize memory use, the MySQLi driver is used directly when possible.
		 */
		
		global $wpdb;
		$dbh = $wpdb->dbh;
		
		self::delete_ser_chunked($key); //Ensure any old values for a chunked value are deleted first
		
		if (self::canCompressValue() && $allowCompression) {
			$data = gzencode(serialize($val));
		}
		else {
			$data = serialize($val);
		}
		
		if (!$wpdb->use_mysqli) {
			$data = bin2hex($data);
		}
		
		$dataLength = strlen($data);
		$chunkSize = intval((self::getDB()->getMaxAllowedPacketBytes() - 50) / 1.2); //Based on max_allowed_packet + 20% for escaping and SQL
		$chunkSize = $chunkSize - ($chunkSize % 2); //Ensure it's even
		$chunkedValueKey = self::ser_chunked_key($key);
		if ($dataLength > $chunkSize) {
			$chunks = 0;
			while (($chunks * $chunkSize) < $dataLength) {
				$dataChunk = substr($data, $chunks * $chunkSize, $chunkSize);
				if ($wpdb->use_mysqli) {
					$chunkKey = $chunkedValueKey . $chunks;
					$stmt = $dbh->prepare("INSERT IGNORE INTO " . self::table() . " (name, val) VALUES (?, ?)");
					$null = NULL;
					$stmt->bind_param("sb", $chunkKey, $null);
					
					if (!$stmt->send_long_data(1, $dataChunk)) {
						wordfence::status(2, 'error', "Error writing value chunk for {$key} (error: {$dbh->error})");
						return false;
					}
					
					if (!$stmt->execute()) {
						wordfence::status(2, 'error', "Error finishing writing value for {$key} (error: {$dbh->error})");
						return false;
					}
				}
				else {
					if (!self::getDB()->queryWrite(sprintf("insert ignore into " . self::table() . " (name, val) values (%%s, X'%s')", $dataChunk), $chunkedValueKey . $chunks)) {
						wordfence::status(2, 'error', "Error writing value chunk for {$key} (error: {$wpdb->last_error})");
						return false;
					}
				}
				$chunks++;
			}
			
			if (!self::getDB()->queryWrite(sprintf("insert ignore into " . self::table() . " (name, val) values (%%s, X'%s')", bin2hex(serialize(array('count' => $chunks)))), $chunkedValueKey . 'header')) {
				wordfence::status(2, 'error', "Error writing value header for {$key}");
				return false;
			}
		}
		else {
			$exists = self::getDB()->querySingle("select name from " . self::table() . " where name='%s'", $key);
			
			if ($wpdb->use_mysqli) {
				if ($exists) {
					$stmt = $dbh->prepare("UPDATE " . self::table() . " SET val=? WHERE name=?");
				}
				else {
					$stmt = $dbh->prepare("INSERT IGNORE INTO " . self::table() . " (val, name) VALUES (?, ?)");
				}
				
				$null = NULL;
				$stmt->bind_param("bs", $null, $key);
				if (!$stmt->send_long_data(0, $data)) {
					wordfence::status(2, 'error', "Error writing value chunk for {$key} (error: {$dbh->error})");
					return false;
				}
				
				if (!$stmt->execute()) {
					wordfence::status(2, 'error', "Error finishing writing value for {$key} (error: {$dbh->error})");
					return false;
				}
			}
			else {
				if ($exists) {
					self::getDB()->queryWrite(sprintf("update " . self::table() . " set val=X'%s' where name=%%s", $data), $key);
				}
				else {
					self::getDB()->queryWrite(sprintf("insert ignore into " . self::table() . " (name, val) values (%%s, X'%s')", $data), $key);
				}
			}
		}
		self::getDB()->flush();
		return true;
	}
	
	private static function delete_ser_chunked($key) {
		$chunkedValueKey = self::ser_chunked_key($key);
		$header = self::getDB()->querySingle("select val from " . self::table() . " where name=%s", $chunkedValueKey . 'header');
		if (!$header) {
			return;
		}
		
		$header = unserialize($header);
		$count = $header['count'];
		for ($i = 0; $i < $count; $i++) {
			self::getDB()->queryWrite("delete from " . self::table() . " where name='%s'", $chunkedValueKey . $i);
		}
		self::getDB()->queryWrite("delete from " . self::table() . " where name='%s'", $chunkedValueKey . 'header');
	}
	
	public static function getTempDir(){
		if(! self::$tmpDirCache){
			$dirs = self::getPotentialTempDirs();
			$finalDir = 'notmp';
			wfUtils::errorsOff();
			foreach($dirs as $dir){
				$dir = rtrim($dir, '/') . '/';
				$fh = @fopen($dir . 'wftmptest.txt', 'w');
				if(! $fh){ continue; }
				$bytes = @fwrite($fh, 'test');
				if($bytes != 4){ @fclose($fh); continue; }
				@fclose($fh);
				if(! @unlink($dir . 'wftmptest.txt')){ continue; }
				$finalDir = $dir;
				break;
			}
			wfUtils::errorsOn();
			self::$tmpDirCache = $finalDir;
		}
		if(self::$tmpDirCache == 'notmp'){
			return false;
		} else {
			return self::$tmpDirCache;
		}
	}
	private static function getPotentialTempDirs() {
		return array(WORDFENCE_PATH . 'tmp/', sys_get_temp_dir(), ABSPATH . 'wp-content/uploads/');
	}
	public static function f($key){
		echo esc_attr(self::get($key));
	}
	public static function cbp($key){
		if(self::get('isPaid') && self::get($key)){
			echo ' checked ';
		}
	}
	public static function cb($key){
		if(self::get($key)){
			echo ' checked ';
		}
	}
	public static function sel($key, $val, $isDefault = false){
		if((! self::get($key)) && $isDefault){ echo ' selected '; }
		if(self::get($key) == $val){ echo ' selected '; }
	}
	public static function getArray(){
		$q = self::getDB()->querySelect("select name, val from " . self::table());
		foreach($q as $row){
			self::$cache[$row['name']] = $row['val'];
		}
		return self::$cache;
	}
	private static function getDB(){
		if(! self::$DB){ 
			self::$DB = new wfDB();
		}
		return self::$DB;
	}
	private static function table(){
		if(! self::$table){
			global $wpdb;
			self::$table = $wpdb->base_prefix . 'wfConfig';
		}
		return self::$table;
	}
	public static function haveAlertEmails(){
		$emails = self::getAlertEmails();
		return sizeof($emails) > 0 ? true : false;
	}
	public static function getAlertEmails(){
		$dat = explode(',', self::get('alertEmails'));
		$emails = array();
		foreach($dat as $email){
			if(preg_match('/\@/', $email)){
				$emails[] = trim($email);
			}
		}
		return $emails;
	}
	public static function getAlertLevel(){
		if(self::get('alertOn_warnings')){
			return 2;
		} else if(self::get('alertOn_critical')){
			return 1;
		} else {
			return 0;
		}
	}
	public static function liveTrafficEnabled(){
		if( (! self::get('liveTrafficEnabled')) || self::get('cacheType') == 'falcon' || self::get('cacheType') == 'php'){ return false; }
		return true;
	}
	public static function enableAutoUpdate(){
		wfConfig::set('autoUpdate', '1');
		wp_clear_scheduled_hook('wordfence_daily_autoUpdate');
		if (is_main_site()) {
			wp_schedule_event(time(), 'daily', 'wordfence_daily_autoUpdate');
		}
	}
	public static function disableAutoUpdate(){
		wfConfig::set('autoUpdate', '0');	
		wp_clear_scheduled_hook('wordfence_daily_autoUpdate');
	}
	public static function autoUpdate(){
		try {
			if(getenv('noabort') != '1' && stristr($_SERVER['SERVER_SOFTWARE'], 'litespeed') !== false){
				$lastEmail = self::get('lastLiteSpdEmail', false);
				if( (! $lastEmail) || (time() - (int)$lastEmail > (86400 * 30))){
					self::set('lastLiteSpdEmail', time());
					 wordfence::alert("Wordfence Upgrade not run. Please modify your .htaccess", "To preserve the integrity of your website we are not running Wordfence auto-update.\n" .
						"You are running the LiteSpeed web server which has been known to cause a problem with Wordfence auto-update.\n" .
						"Please go to your website now and make a minor change to your .htaccess to fix this.\n" .
						"You can find out how to make this change at:\n" .
						"https://docs.wordfence.com/en/LiteSpeed_aborts_Wordfence_scans_and_updates._How_do_I_prevent_that%3F\n" .
						"\nAlternatively you can disable auto-update on your website to stop receiving this message and upgrade Wordfence manually.\n",
						'127.0.0.1'
						);
				}
				return;
			}
			require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
			require_once(ABSPATH . 'wp-admin/includes/misc.php');
			/* We were creating show_message here so that WP did not write to STDOUT. This had the strange effect of throwing an error about redeclaring show_message function, but only when a crawler hit the site and triggered the cron job. Not a human. So we're now just require'ing misc.php which does generate output, but that's OK because it is a loopback cron request.  
			if(! function_exists('show_message')){ 
				function show_message($msg = 'null'){}
			}
			*/
			if(! defined('FS_METHOD')){ 
				define('FS_METHOD', 'direct'); //May be defined already and might not be 'direct' so this could cause problems. But we were getting reports of a warning that this is already defined, so this check added. 
			}
			require_once(ABSPATH . 'wp-includes/update.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			wp_update_plugins();
			ob_start();
			$upgrader = new Plugin_Upgrader();
			$upret = $upgrader->upgrade(WORDFENCE_BASENAME);
			if($upret){
				$cont = file_get_contents(WORDFENCE_FCPATH);
				if(wfConfig::get('alertOn_update') == '1' && preg_match('/Version: (\d+\.\d+\.\d+)/', $cont, $matches) ){
					wordfence::alert("Wordfence Upgraded to version " . $matches[1], "Your Wordfence installation has been upgraded to version " . $matches[1], '127.0.0.1');
				}
			}
			$output = @ob_get_contents();
			@ob_end_clean();
		} catch(Exception $e){}
	}
	
	/**
	 * .htaccess file contents to disable all script execution in a given directory.
	 */
	private static $_disable_scripts_htaccess = '# BEGIN Wordfence code execution protection
<IfModule mod_php5.c>
php_flag engine 0
</IfModule>

AddHandler cgi-script .php .phtml .php3 .pl .py .jsp .asp .htm .shtml .sh .cgi
Options -ExecCGI
# END Wordfence code execution protection
';
	
	private static function _uploadsHtaccessFilePath() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'] . '/.htaccess';
	}

	/**
	 * Add/Merge .htaccess file in the uploads directory to prevent code execution.
	 *
	 * @return bool
	 * @throws wfConfigException
	 */
	public static function disableCodeExecutionForUploads() {
		$uploads_htaccess_file_path = self::_uploadsHtaccessFilePath();
		$uploads_htaccess_has_content = false;
		if (file_exists($uploads_htaccess_file_path)) {
			$htaccess_contents = file_get_contents($uploads_htaccess_file_path);
			
			// htaccess exists and contains our htaccess code to disable script execution, nothing more to do
			if (strpos($htaccess_contents, self::$_disable_scripts_htaccess) !== false) {
				return true;
			}
			$uploads_htaccess_has_content = strlen(trim($htaccess_contents)) > 0;
		}
		if (@file_put_contents($uploads_htaccess_file_path, ($uploads_htaccess_has_content ? "\n\n" : "") . self::$_disable_scripts_htaccess, FILE_APPEND | LOCK_EX) === false) {
			throw new wfConfigException("Unable to save the .htaccess file needed to disable script execution in the uploads directory.  Please check your permissions on that directory.");
		}
		return true;
	}

	/**
	 * Remove script execution protections for our the .htaccess file in the uploads directory.
	 *
	 * @return bool
	 * @throws wfConfigException
	 */
	public static function removeCodeExecutionProtectionForUploads() {
		$uploads_htaccess_file_path = self::_uploadsHtaccessFilePath();
		if (file_exists($uploads_htaccess_file_path)) {
			$htaccess_contents = file_get_contents($uploads_htaccess_file_path);

			// Check that it is in the file
			if (strpos($htaccess_contents, self::$_disable_scripts_htaccess) !== false) {
				$htaccess_contents = str_replace(self::$_disable_scripts_htaccess, '', $htaccess_contents);

				$error_message = "Unable to remove code execution protections applied to the .htaccess file in the uploads directory.  Please check your permissions on that file.";
				if (strlen(trim($htaccess_contents)) === 0) {
					// empty file, remove it
					if (!@unlink($uploads_htaccess_file_path)) {
						throw new wfConfigException($error_message);
					}

				} elseif (@file_put_contents($uploads_htaccess_file_path, $htaccess_contents, LOCK_EX) === false) {
					throw new wfConfigException($error_message);
				}
			}
		}
		return true;
	}
}

class wfConfigException extends Exception {}

?>
