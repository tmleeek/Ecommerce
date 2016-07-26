<?php

class Unleaded_YMM_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{
	public function match(Zend_Controller_Request_Http $request)
	{
		//checking before even try to find out that current module
		if (!preg_match('/^\/models\/.*$/', $request->getRequestUri())) {
			return false;
		}

		$this->fetchDefault();

		$front = $this->getFront();
		$path = trim($request->getPathInfo(), '/');

		if ($path) {
			$p = explode('/', $path);
		} else {
			$p = explode('/', $this->_getDefaultPath());
		}

		// get module name
		$module = 'models';

		/**
		 * Searching router args by module name from route using it as key
		 */
		$modules = $this->getModuleByFrontName($module);

		if ($modules === false) {
			return false;
		}

		// checks after we found out that this router should be used for current module
		if (!$this->_afterModuleMatch()) {
			return false;
		}

		/**
		 * Going through modules to find appropriate controller
		 */
		$found = false;
		foreach ($modules as $realModule) {
			$request->setRouteName($this->getRouteByFrontName($module));

			$controller = 'index';

			$action = 'index';

			//checking if this place should be secure
			$this->_checkShouldBeSecure($request, '/'.$module.'/'.$controller.'/'.$action);

			$controllerClassName = $this->_validateControllerClassName($realModule, $controller);
			if (!$controllerClassName) {
				continue;
			}

			// instantiate controller class
			$controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $front->getResponse());

		}

		// set values only after all the checks are done
		$request->setModuleName('models');
		$request->setControllerName('index');
		$request->setActionName('index');
		$request->setControllerModule('Unleaded_YMM');

		// set parameters from pathinfo
		for ($i = 3, $l = sizeof($p); $i < $l; $i += 2) {
			$request->setParam($p[$i], isset($p[$i+1]) ? urldecode($p[$i+1]) : '');
		}

		// dispatch action
		$request->setDispatched(true);
		$controllerInstance->dispatch($action);

		return true;
	}
}