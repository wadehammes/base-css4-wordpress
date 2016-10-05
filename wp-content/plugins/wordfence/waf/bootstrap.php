<?php

/*
	php_value auto_prepend_file ~/wp-content/plugins/wordfence/waf/bootstrap.php
*/

if (!defined('WFWAF_AUTO_PREPEND')) {
	define('WFWAF_AUTO_PREPEND', true);
}

require_once dirname(__FILE__) . '/wfWAFUserIPRange.php';
require_once dirname(__FILE__) . '/../vendor/wordfence/wf-waf/src/init.php';

class wfWAFWordPressRequest extends wfWAFRequest {

	/**
	 * @param wfWAFRequest|null $request
	 * @return wfWAFRequest
	 */
	public static function createFromGlobals($request = null) {
		if (version_compare(phpversion(), '5.3.0') >= 0) {
			$class = get_called_class();
			$request = new $class();
		} else {
			$request = new self();
		}
		return parent::createFromGlobals($request);
	}

	public function getIP() {
		$howGet = wfWAF::getInstance()->getStorageEngine()->getConfig('howGetIPs');
		if (is_string($howGet) && is_array($_SERVER) && array_key_exists($howGet, $_SERVER)) {
			$ips[] = $_SERVER[$howGet];
		}
		$ips[] = $ip = (is_array($_SERVER) && array_key_exists('REMOTE_ADDR', $_SERVER)) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
		foreach ($ips as $ip) {
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
				return $ip;
			}
		}
		return $ip;
	}
}

class wfWAFWordPressObserver extends wfWAFBaseObserver {

	public function beforeRunRules() {
		// Whitelisted URLs (in WAF config)
		$whitelistedURLs = wfWAF::getInstance()->getStorageEngine()->getConfig('whitelistedURLs');
		if ($whitelistedURLs) {
			$whitelistPattern = "";
			foreach ($whitelistedURLs as $whitelistedURL) {
				$whitelistPattern .= preg_replace('/\\\\\*/', '.*?', preg_quote($whitelistedURL, '/')) . '|';
			}
			$whitelistPattern = '/^(?:' . substr($whitelistPattern, 0, -1) . ')$/i';

			wfWAFRule::create(wfWAF::getInstance(), 0x8000000, 'rule', 'whitelist', 0, 'User Supplied Whitelisted URL', 'allow',
				new wfWAFRuleComparisonGroup(
					new wfWAFRuleComparison(wfWAF::getInstance(), 'match', $whitelistPattern, array(
						'request.uri',
					))
				)
			)->evaluate();
		}

		// Whitelisted IPs (Wordfence config)
		$whitelistedIPs = wfWAF::getInstance()->getStorageEngine()->getConfig('whitelistedIPs');
		if ($whitelistedIPs) {
			if (!is_array($whitelistedIPs)) {
				$whitelistedIPs = explode(',', $whitelistedIPs);
			}
			foreach ($whitelistedIPs as $whitelistedIP) {
				$ipRange = new wfWAFUserIPRange($whitelistedIP);
				if ($ipRange->isIPInRange(wfWAF::getInstance()->getRequest()->getIP())) {
					throw new wfWAFAllowException('Wordfence whitelisted IP.');
				}
			}
		}
	}
	
	public function afterRunRules()
	{
		//wfWAFLogException
		$watchedIPs = wfWAF::getInstance()->getStorageEngine()->getConfig('watchedIPs');
		if ($watchedIPs) {
			if (!is_array($watchedIPs)) {
				$watchedIPs = explode(',', $watchedIPs);
			}
			foreach ($watchedIPs as $watchedIP) {
				$ipRange = new wfWAFUserIPRange($watchedIP);
				if ($ipRange->isIPInRange(wfWAF::getInstance()->getRequest()->getIP())) {
					throw new wfWAFLogException('Wordfence watched IP.');
				}
			}
		}
	}
}

/**
 *
 */
class wfWAFWordPress extends wfWAF {

	/** @var wfWAFRunException */
	private $learningModeAttackException;

	/**
	 * @param wfWAFBlockException $e
	 * @param int $httpCode
	 */
	public function blockAction($e, $httpCode = 403) {
		if ($this->isInLearningMode()) {
			register_shutdown_function(array(
				$this, 'whitelistFailedRulesIfNot404',
			));
			$this->getStorageEngine()->logAttack($e->getFailedRules(), $e->getParamKey(), $e->getParamValue(), $e->getRequest());
			$this->setLearningModeAttackException($e);
		} else {
			parent::blockAction($e, $httpCode);
		}
	}

	/**
	 * @param wfWAFBlockXSSException $e
	 * @param int $httpCode
	 */
	public function blockXSSAction($e, $httpCode = 403) {
		if ($this->isInLearningMode()) {
			register_shutdown_function(array(
				$this, 'whitelistFailedRulesIfNot404',
			));
			$this->getStorageEngine()->logAttack($e->getFailedRules(), $e->getParamKey(), $e->getParamValue(), $e->getRequest());
			$this->setLearningModeAttackException($e);
		} else {
			parent::blockXSSAction($e, $httpCode);
		}
	}

	/**
	 *
	 */
	public function runCron() {
		/**
		 * Removed sending attack data. Attack data is sent in @see wordfence::veryFirstAction
		 */
		$cron = $this->getStorageEngine()->getConfig('cron');
		if (is_array($cron)) {
			/** @var wfWAFCronEvent $event */
			foreach ($cron as $index => $event) {
				$event->setWaf($this);
				if ($event->isInPast()) {
					$event->fire();
					$newEvent = $event->reschedule();
					if ($newEvent instanceof wfWAFCronEvent && $newEvent !== $event) {
						$cron[$index] = $newEvent;
					} else {
						unset($cron[$index]);
					}
				}
			}
		}
		$this->getStorageEngine()->setConfig('cron', $cron);
	}

	/**
	 *
	 */
	public function whitelistFailedRulesIfNot404() {
		/** @var WP_Query $wp_query */
		global $wp_query;
		if (defined('ABSPATH') &&
			isset($wp_query) && class_exists('WP_Query') && $wp_query instanceof WP_Query &&
			method_exists($wp_query, 'is_404') && $wp_query->is_404() &&
			function_exists('is_admin') && !is_admin()) {
			return;
		}
		$this->whitelistFailedRules();
	}

	/**
	 * @param $ip
	 * @return mixed
	 */
	public function isIPBlocked($ip) {
		return false;
	}
	
	public function uninstall() {
		parent::uninstall();
		@unlink(rtrim(WFWAF_LOG_PATH . '/') . '/.htaccess');
		@rmdir(WFWAF_LOG_PATH);
	}

	/**
	 * @return wfWAFRunException
	 */
	public function getLearningModeAttackException() {
		return $this->learningModeAttackException;
	}

	/**
	 * @param wfWAFRunException $learningModeAttackException
	 */
	public function setLearningModeAttackException($learningModeAttackException) {
		$this->learningModeAttackException = $learningModeAttackException;
	}
}

if (!defined('WFWAF_LOG_PATH')) {
	define('WFWAF_LOG_PATH', WP_CONTENT_DIR . '/wflogs/');
}
if (!is_dir(WFWAF_LOG_PATH)) {
	@mkdir(WFWAF_LOG_PATH, 0775);
	@chmod(WFWAF_LOG_PATH, 0775);
	@file_put_contents(rtrim(WFWAF_LOG_PATH . '/') . '/.htaccess', <<<APACHE
<IfModule mod_authz_core.c>
	Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
	Order deny,allow
	Deny from all
</IfModule>
APACHE
	);
	@chmod(rtrim(WFWAF_LOG_PATH . '/') . '/.htaccess', 0664);
}


wfWAF::setInstance(new wfWAFWordPress(
	wfWAFWordPressRequest::createFromGlobals(),
	new wfWAFStorageFile(WFWAF_LOG_PATH . 'attack-data.php', WFWAF_LOG_PATH . 'ips.php', WFWAF_LOG_PATH . 'config.php', WFWAF_LOG_PATH . 'wafRules.rules')
));
wfWAF::getInstance()->getEventBus()->attach(new wfWAFWordPressObserver);

try {
	$rulesFiles = array(
		WFWAF_LOG_PATH . 'rules.php',
		// WFWAF_PATH . 'rules.php',
	);
	foreach ($rulesFiles as $rulesFile) {
		if (!file_exists($rulesFile)) {
			@touch($rulesFile);
		}
		@chmod($rulesFile, 0664);
		if (is_writable($rulesFile)) {
			wfWAF::getInstance()->setCompiledRulesFile($rulesFile);
			break;
		}
	}

	if (!file_exists(wfWAF::getInstance()->getCompiledRulesFile()) || !filesize(wfWAF::getInstance()->getCompiledRulesFile())) {
		try {
			if (is_writable(wfWAF::getInstance()->getCompiledRulesFile()) &&
				wfWAF::getInstance()->getStorageEngine()->getConfig('apiKey') !== null &&
				wfWAF::getInstance()->getStorageEngine()->getConfig('createInitialRulesDelay') < time()
			) {
				$event = new wfWAFCronFetchRulesEvent(time() - 60);
				$event->setWaf(wfWAF::getInstance());
				$event->fire();
				wfWAF::getInstance()->getStorageEngine()->setConfig('createInitialRulesDelay', time() + (5 * 60));
			}
		} catch (wfWAFBuildRulesException $e) {
			// Log this somewhere
			error_log($e->getMessage());
		} catch (Exception $e) {
			// Suppress this
			error_log($e->getMessage());
		}
	}

	if (WFWAF_DEBUG && file_exists(wfWAF::getInstance()->getStorageEngine()->getRulesDSLCacheFile())) {
		try {
			wfWAF::getInstance()->updateRuleSet(file_get_contents(wfWAF::getInstance()->getStorageEngine()->getRulesDSLCacheFile()), false);
		} catch (wfWAFBuildRulesException $e) {
			$GLOBALS['wfWAFDebugBuildException'] = $e;
		} catch (Exception $e) {
			$GLOBALS['wfWAFDebugBuildException'] = $e;
		}
	}

	try {
		wfWAF::getInstance()->run();
	} catch (wfWAFBuildRulesException $e) {
		// Log this
		error_log($e->getMessage());
	} catch (Exception $e) {
		// Suppress this
		error_log($e->getMessage());
	}

} catch (wfWAFStorageFileConfigException $e) {
	// Let this request through for now
	error_log($e->getMessage());

} catch (wfWAFStorageFileException $e) {
	// We need to choose another storage engine here.
}
