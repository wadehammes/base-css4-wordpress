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

		//Register the module tab.
		if ( ($this->tabSlug !== '') && is_string($this->tabSlug) ) {
			add_action('admin_menu_editor-tabs', array($this, 'addTab'), $this->tabOrder);
			add_action('admin_menu_editor-section-' . $this->tabSlug, array($this, 'displaySettingsPage'));
		}
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
			array(
				'page' => 'menu_editor',
				'sub_section' => $this->tabSlug
			),
			$queryParameters
		);
		return add_query_arg($queryParameters, admin_url('options-general.php'));
	}

	protected function outputMainTemplate() {
		return $this->outputTemplate($this->moduleId);
	}

	protected function outputTemplate($name) {
		$templateFile = $this->moduleDir . '/' . $name . '-template.php';
		if ( file_exists($templateFile) ) {
			/** @noinspection PhpIncludeInspection */
			require $templateFile;
			return true;
		}
		return false;
	}
}