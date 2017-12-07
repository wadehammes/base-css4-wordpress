<?php

class wfUpdateCheck {

	private $needs_core_update = false;
	private $core_update_version = 0;
	private $plugin_updates = array();
	private $theme_updates = array();
	private $api = null;

	public function __construct() {
		$this->api = new wfAPI(wfConfig::get('apiKey'), wfUtils::getWPVersion());
	}

	/**
	 * @return bool
	 */
	public function needsAnyUpdates() {
		return $this->needsCoreUpdate() || count($this->getPluginUpdates()) > 0 || count($this->getThemeUpdates()) > 0;
	}

	/**
	 * Check for any core, plugin or theme updates.
	 *
	 * @return $this
	 */
	public function checkAllUpdates($useCachedValued = true) {
		return $this->checkCoreUpdates($useCachedValued)
			->checkPluginUpdates($useCachedValued)
			->checkThemeUpdates($useCachedValued);
	}

	/**
	 * Check if there is an update to the WordPress core.
	 *
	 * @return $this
	 */
	public function checkCoreUpdates($useCachedValued = true) {
		$this->needs_core_update = false;

		if (!function_exists('wp_version_check')) {
			require_once(ABSPATH . WPINC . '/update.php');
		}
		if (!function_exists('get_preferred_from_update_core')) {
			require_once(ABSPATH . 'wp-admin/includes/update.php');
		}
		
		include( ABSPATH . WPINC . '/version.php' ); //defines $wp_version
		
		$update_core = get_preferred_from_update_core();
		if ($useCachedValued && isset($update_core->last_checked) && isset($update_core->version_checked) && 12 * HOUR_IN_SECONDS > (time() - $update_core->last_checked) && $update_core->version_checked == $wp_version) { //Duplicate of _maybe_update_core, which is a private call
			//Do nothing, use cached value
		}
		else {
			wp_version_check();
			$update_core = get_preferred_from_update_core();
		}

		if (isset($update_core->response) && $update_core->response == 'upgrade') {
			$this->needs_core_update = true;
			$this->core_update_version = $update_core->current;
		}

		return $this;
	}

	/**
	 * Check if any plugins need an update.
	 *
	 * @return $this
	 */
	public function checkPluginUpdates($useCachedValued = true) {
		$this->plugin_updates = array();

		if (!function_exists('wp_update_plugins')) {
			require_once(ABSPATH . WPINC . '/update.php');
		}

		if (!function_exists('plugins_api')) {
			require_once(ABSPATH . '/wp-admin/includes/plugin-install.php');
		}
		
		$update_plugins = get_site_transient('update_plugins');
		if ($useCachedValued && isset($update_plugins->last_checked) && 12 * HOUR_IN_SECONDS > (time() - $update_plugins->last_checked)) { //Duplicate of _maybe_update_plugins, which is a private call
			//Do nothing, use cached value
		}
		else {
			wp_update_plugins();
			$update_plugins = get_site_transient('update_plugins');
		}

		if ($update_plugins && !empty($update_plugins->response)) {
			foreach ($update_plugins->response as $plugin => $vals) {
				if (!function_exists('get_plugin_data')) {
					require_once ABSPATH . '/wp-admin/includes/plugin.php';
				}
				
				$pluginFile = wfUtils::getPluginBaseDir() . $plugin;
				$valsArray = (array) $vals;
				
				$data = get_plugin_data($pluginFile);
				$data['pluginFile'] = $pluginFile;
				$data['newVersion'] = (isset($valsArray['new_version']) ? $valsArray['new_version'] : 'Unknown');
				$data['slug'] = (isset($valsArray['slug']) ? $valsArray['slug'] : null);
				$data['wpURL'] = (isset($valsArray['url']) ? rtrim($valsArray['url'], '/') : null);

				//Check the vulnerability database
				if (isset($valsArray['slug']) && isset($data['Version'])) {
					$data['vulnerabilityPatched'] = $this->isPluginVulnerable($valsArray['slug'], $data['Version']);
				}
				else {
					$data['vulnerabilityPatched'] = false;
				}
				

				$this->plugin_updates[] = $data;
			}
		}

		return $this;
	}

	/**
	 * Check if any themes need an update.
	 *
	 * @return $this
	 */
	public function checkThemeUpdates($useCachedValued = true) {
		$this->theme_updates = array();

		if (!function_exists('wp_update_themes')) {
			require_once(ABSPATH . WPINC . '/update.php');
		}
		
		$update_themes = get_site_transient('update_themes');
		if ($useCachedValued && isset($update_themes->last_checked) && 12 * HOUR_IN_SECONDS > (time() - $update_themes->last_checked)) { //Duplicate of _maybe_update_themes, which is a private call
			//Do nothing, use cached value
		}
		else {
			wp_update_themes();
			$update_themes = get_site_transient('update_themes');
		}

		if ($update_themes && (!empty($update_themes->response))) {
			if (!function_exists('wp_get_themes')) {
				require_once ABSPATH . '/wp-includes/theme.php';
			}
			$themes = wp_get_themes();
			foreach ($update_themes->response as $theme => $vals) {
				foreach ($themes as $name => $themeData) {
					if (strtolower($name) == $theme) {
						$vulnerabilityPatched = false;
						if (isset($themeData['Version'])) {
							$vulnerabilityPatched = $this->isThemeVulnerable($theme, $themeData['Version']);
						}
						
						$this->theme_updates[] = array(
							'newVersion' => (isset($vals['new_version']) ? $vals['new_version'] : 'Unknown'),
							'package'    => (isset($vals['package']) ? $vals['package'] : null),
							'URL'        => (isset($vals['url']) ? $vals['url'] : null),
							'Name'       => $themeData['Name'],
							'name'       => $themeData['Name'],
							'version'    => $themeData['Version'],
							'vulnerabilityPatched' => $vulnerabilityPatched
						);
					}
				}
			}
		}
		return $this;
	}
	
	public function checkAllVulnerabilities() {
		$this->checkPluginVulnerabilities();
		$this->checkThemeVulnerabilities();
	}
	
	public function checkPluginVulnerabilities() {
		if (!function_exists('wp_update_plugins')) {
			require_once(ABSPATH . WPINC . '/update.php');
		}
		
		if (!function_exists('plugins_api')) {
			require_once(ABSPATH . '/wp-admin/includes/plugin-install.php');
		}
		
		$this->checkPluginUpdates();
		$update_plugins = get_site_transient('update_plugins');
		
		$vulnerabilities = array();
		if ($update_plugins && !empty($update_plugins->response)) {
			if (!function_exists('get_plugin_data'))
			{
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}
			
			foreach ($update_plugins->response as $plugin => $vals) {
				$pluginFile = wfUtils::getPluginBaseDir() . $plugin;
				$valsArray = (array) $vals;
				$data = get_plugin_data($pluginFile);
				
				$record = array();
				$record['slug'] = (isset($valsArray['slug']) ? $valsArray['slug'] : null);
				$record['toVersion'] = (isset($valsArray['new_version']) ? $valsArray['new_version'] : 'Unknown');
				$record['fromVersion'] = (isset($data['Version']) ? $data['Version'] : 'Unknown');
				$record['vulnerable'] = false;
				$vulnerabilities[] = $record;
			}
			
			try {
				$result = $this->api->call('plugin_vulnerability_check', array(), array(
					'plugins' => json_encode($vulnerabilities),
				));
				
				foreach ($vulnerabilities as &$v) {
					$vulnerableList = $result['vulnerable'];
					foreach ($vulnerableList as $r) {
						if ($r['slug'] == $v['slug']) {
							$v['vulnerable'] = !!$r['vulnerable']; 
							break;
						}
					}
				}
			}
			catch (Exception $e) {
				//Do nothing
			}
			
			wfConfig::set_ser('vulnerabilities_plugin', $vulnerabilities);
		}
	}
	
	public function checkThemeVulnerabilities() {
		if (!function_exists('wp_update_themes')) {
			require_once(ABSPATH . WPINC . '/update.php');
		}
		
		if (!function_exists('plugins_api')) {
			require_once(ABSPATH . '/wp-admin/includes/plugin-install.php');
		}
		
		$this->checkThemeUpdates();
		$update_themes = get_site_transient('update_themes');
		
		$vulnerabilities = array();
		if ($update_themes && !empty($update_themes->response)) {
			if (!function_exists('get_plugin_data'))
			{
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}
			
			foreach ($update_themes->response as $themeSlug => $vals) {
				
				$valsArray = (array) $vals;
				$theme = wp_get_theme($themeSlug);
				
				$record = array();
				$record['slug'] = $themeSlug;
				$record['toVersion'] = (isset($valsArray['new_version']) ? $valsArray['new_version'] : 'Unknown');
				$record['fromVersion'] = $theme->version;
				$record['vulnerable'] = false;
				$vulnerabilities[] = $record;
			}
			
			try {
				$result = $this->api->call('theme_vulnerability_check', array(), array(
					'themes' => json_encode($vulnerabilities),
				));
				
				foreach ($vulnerabilities as &$v) {
					$vulnerableList = $result['vulnerable'];
					foreach ($vulnerableList as $r) {
						if ($r['slug'] == $v['slug']) {
							$v['vulnerable'] = !!$r['vulnerable'];
							break;
						}
					}
				}
			}
			catch (Exception $e) {
				//Do nothing
			}
			
			wfConfig::set_ser('vulnerabilities_theme', $vulnerabilities);
		}
	}
	
	public function isPluginVulnerable($slug, $version) {
		return $this->_isSlugVulnerable('vulnerabilities_plugin', $slug, $version);
	}
	
	public function isThemeVulnerable($slug, $version) {
		return $this->_isSlugVulnerable('vulnerabilities_theme', $slug, $version);
	}
	
	private function _isSlugVulnerable($vulnerabilitiesKey, $slug, $version) {
		$vulnerabilities = wfConfig::get_ser($vulnerabilitiesKey, array());
		foreach ($vulnerabilities as $v) {
			if ($v['slug'] == $slug) {
				if ($v['fromVersion'] == 'Unknown' && $v['toVersion'] == 'Unknown') {
					return $v['vulnerable'];
				}
				else if ($v['toVersion'] == 'Unknown' && version_compare($version, $v['fromVersion']) >= 0) {
					return $v['vulnerable'];
				}
				else if ($v['fromVersion'] == 'Unknown' && version_compare($version, $v['toVersion']) < 0) {
					return $v['vulnerable'];
				}
				else if (version_compare($version, $v['fromVersion']) >= 0 && version_compare($version, $v['toVersion']) < 0) {
					return $v['vulnerable'];
				}
			}
		}
		return false;
	}

	/**
	 * @return boolean
	 */
	public function needsCoreUpdate() {
		return $this->needs_core_update;
	}

	/**
	 * @return int
	 */
	public function getCoreUpdateVersion() {
		return $this->core_update_version;
	}

	/**
	 * @return array
	 */
	public function getPluginUpdates() {
		return $this->plugin_updates;
	}

	/**
	 * @return array
	 */
	public function getThemeUpdates() {
		return $this->theme_updates;
	}
}
