<?php
abstract class ameModule {
	protected $tabSlug = '';
	protected $tabTitle = '';
	protected $tabOrder = 10;

	protected $moduleId = '';
	protected $moduleDir = '';

	/**
	 * @var WPMenuEditor
	 */
	protected $menuEditor;

	public function __construct($menuEditor) {
		$this->menuEditor = $menuEditor;

		if ( class_exists('ReflectionClass', false) ) {
			$reflector = new ReflectionClass(get_class($this));
			$this->moduleDir = dirname($reflector->getFileName());
			$this->moduleId = basename($this->moduleDir);
		}

		if ( !$this->isEnabledForRequest() ) {
			return;
		}

		add_action('admin_menu_editor-register_scripts', array($this, 'registerScripts'));

		//Register the module tab.
		if ( ($this->tabSlug !== '') && is_string($this->tabSlug) ) {
			add_action('admin_menu_editor-tabs', array($this, 'addTab'), $this->tabOrder);
			add_action('admin_menu_editor-section-' . $this->tabSlug, array($this, 'displaySettingsPage'));

			add_action('admin_menu_editor-enqueue_scripts-' . $this->tabSlug, array($this, 'enqueueTabScripts'));
			add_action('admin_menu_editor-enqueue_styles-' . $this->tabSlug, array($this, 'enqueueTabStyles'));
		}
	}

	/**
	 * Does this module need to do anything for the current request?
	 *
	 * For example, some modules work in the normal dashboard but not in the network admin.
	 * Other modules don't need to run during AJAX requests or when WP is running Cron jobs.
	 */
	protected function isEnabledForRequest() {
		return true;
	}

	public function addTab($tabs) {
		$tabs[$this->tabSlug] = !empty($this->tabTitle) ? $this->tabTitle : $this->tabSlug;
		return $tabs;
	}

	public function displaySettingsPage() {
		$this->menuEditor->display_settings_page_header();

		if ( !$this->outputMainTemplate() ) {
			printf("[ %1\$s : Module \"%2\$s\" doesn't have a primary template. ]", __METHOD__, $this->moduleId);
		}

		$this->menuEditor->display_settings_page_footer();
	}

	protected function getTabUrl($queryParameters = array()) {
		$queryParameters = array_merge(
			array('sub_section' => $this->tabSlug),
			$queryParameters
		);
		return $this->menuEditor->get_plugin_page_url($queryParameters);
	}

	protected function outputMainTemplate() {
		return $this->outputTemplate($this->moduleId);
	}

	protected function outputTemplate($name) {
		$templateFile = $this->moduleDir . '/' . $name . '-template.php';
		if ( file_exists($templateFile) ) {
			/** @noinspection PhpUnusedLocalVariableInspection Used in some templates. */
			$moduleTabUrl = $this->getTabUrl();

			/** @noinspection PhpIncludeInspection */
			require $templateFile;
			return true;
		}
		return false;
	}

	public function registerScripts() {
		//Override this method to register scripts.
	}

	public function enqueueTabScripts() {
		//Override this method to add scripts to the $this->tabSlug tab.
	}

	public function enqueueTabStyles() {
		//Override this method to add stylesheets to the $this->tabSlug tab.
	}
}