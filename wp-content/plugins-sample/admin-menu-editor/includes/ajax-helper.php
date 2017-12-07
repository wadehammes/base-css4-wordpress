<?php
class ameAjaxAction {
	protected $action = '';
	protected $handler;

	protected $requiredParameters = array();
	protected $defaultParameters = array();
	protected $checkAuthorization;

	public function __construct($uniqueActionName = null, $handler = null) {
		if (isset($uniqueActionName)) {
			$this->action = $uniqueActionName;
		}
		if (isset($handler)) {
			$this->handler = $handler;
		}

		if (empty($this->action)) {
			throw new LogicException(sprintf(
				'AJAX action name is missing. You must either pass it to the %1$s constructor '
				. 'or give the %1$s::$action property a valid default value.',
				get_class($this)
			));
		}

		$hookName = 'wp_ajax_' . $this->action;
		if (has_action($hookName)) {
			throw new RuntimeException(sprintf('The action name "%s" is already in use.', $this->action));
		}
		add_action('wp_ajax_' . $this->action, array($this, '_processRequest'));
	}

	/**
	 * @access protected
	 */
	public function _processRequest() {
		//Check nonce.
		if (!check_ajax_referer($this->action, false, false)) {
			$this->exitWithError(
				'Access denied. Invalid nonce',
				'invalid_nonce'
			);
		}

		$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');

		//Retrieve request parameters.
		$params = array();
		if ($method === 'GET') {
			$params = $_GET;
		} else if ($method === 'POST') {
			$params = $_POST;
		}
		
		//Remove magic quotes. WordPress applies them in wp-settings.php.
		if (did_action('sanitize_comment_cookies') && function_exists('wp_magic_quotes')) {
			$params = wp_unslash($params);
		}

		//Verify that all of the required parameters are present. Empty strings are not allowed.
		foreach($this->requiredParameters as $name) {
			if (!isset($params[$name]) || ($params[$name] === '')) {
				$this->exitWithError(
					sprintf('The required parameter "%s" is missing or empty.', $name),
					'missing_required_parameter'
				);
			}
		}

		//Apply defaults.
		$params = array_merge($this->defaultParameters, $params);

		//Run custom authorization checks.
		$isAllowed = $this->isUserAuthorized($params);
		if ($isAllowed instanceof WP_Error) {
			$this->exitWithError($isAllowed->get_error_message(), $isAllowed->get_error_code());
		} else if (!$isAllowed) {
			$this->exitWithError(
				sprintf('You don\'t have permission to perform the "%s" action.', $this->action),
				'access_denied'
			);
		}

		//Finally, perform the action.
		$response = $this->handleAction($params);
		if ($response instanceof WP_Error) {
			$this->exitWithError($response->get_error_message(), $response->get_error_code());
		}
		$this->outputResponse($response);
		exit;
	}

	protected function handleAction($params) {
		if (is_callable($this->handler)) {
			return call_user_func($this->handler, $params);
		} else {
			$this->exitWithError(
				sprintf(
					'There is no request handler assigned to the "%1$s" action. '
					. 'Either override the %3$s method or set %2$s::$handler to a valid callback.',
					$this->action,
					__CLASS__,
					__METHOD__
				),
				'missing_ajax_handler'
			);
			return null;
		}
	}

	protected function exitWithError($message, $code = null) {
		if ( ($message === '') && !empty($code) ) {
			$message = $code;
		}

		$response = array(
			'error' => array(
				'message' => $message,
				'code' => $code,
			)
		);
		$this->outputResponse($response);
		exit;
	}

	protected function outputResponse($response) {
		header('Content-Type: application/json');
		echo json_encode($response);
	}

	/**
	 * Check if the current user is authorized to perform this action.
	 *
	 * @param array $params Request parameters.
	 * @return bool|WP_Error
	 */
	protected function isUserAuthorized($params) {
		if (isset($this->checkAuthorization)) {
			return call_user_func($this->checkAuthorization, $params);
		}
		return true;
	}

	//Just a bunch of fluent setters.
	//-------------------------------

	/**
	 * @param string ...$param One or more parameter names.
	 * @return $this
	 */
	public function setRequiredParams($param) {
		$params = func_get_args();
		if (count($params) === 1 && is_array($params[0])) {
			$params = $params[0];
		}

		$this->requiredParameters = $params;
		return $this;
	}

	/**
	 * @param callable $handler
	 * @return $this
	 */
	public function setHandler($handler) {
		$this->handler = $handler;
		return $this;
	}

	/**
	 * @param callable $callback
	 * @return $this
	 */
	public function setAuthCallback($callback) {
		$this->checkAuthorization = $callback;
		return $this;
	}
}