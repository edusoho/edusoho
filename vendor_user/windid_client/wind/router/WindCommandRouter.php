<?php
Wind::import('WIND:router.AbstractWindRouter');
/**
 * 命令行路由，默认路由规则 php index.php [-m default] [-c index] [-a run] [-p id1 id2] [--help]
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindCommandRouter.php 3859 2012-12-18 09:25:51Z yishuo $
 * @package command
 */
class WindCommandRouter extends AbstractWindRouter {
	protected $moduleKey = '-m,module,--module';
	protected $controllerKey = '-c,controller,--controller';
	protected $actionKey = '-a,action,--action';
	protected $helpKey = '-h,help,--help';
	protected $paramKey = '-p,param,--param';
	protected $help = false;
	protected $cmd = '';
	/**
	 * @var WindCommandRequest
	 */
	private $request = null;
	
	/* (non-PHPdoc)
	 * @see AbstractWindRouter::route()
	 */
	public function route($request) {
		$this->request = $request;
		$this->_action = $this->action;
		$this->_controller = $this->controller;
		$this->_module = $this->module;
		if (!empty($this->_config['routes'])) {
			$params = $this->getHandler()->handle($request);
			$this->setParams($params, $request);
		} else {
			$args = $request->getRequest('argv', array());
			$this->cmd = $args[0];
			$_count = count($args);
			for ($i = 1; $i < $_count; $i++) {
				if (in_array($args[$i], explode(',', $this->helpKey))) {
					$this->help = true;
				} elseif (in_array($args[$i], explode(',', $this->moduleKey))) {
					$this->module = $args[++$i];
				} elseif (in_array($args[$i], explode(',', $this->controllerKey))) {
					$this->controller = $args[++$i];
				} elseif (in_array($args[$i], explode(',', $this->actionKey))) {
					$this->action = $args[++$i];
				} elseif (in_array($args[$i], explode(',', $this->paramKey))) {
					$_SERVER['argv'] = array_slice($args, $i + 1);
					break;
				}
			}
		}
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindRouter::assemble()
	 */
	public function assemble($action, $args = array(), $route = '') {
		return '';
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindRouter::setParams()
	 */
	protected function setParams($params, $request) {
		/* @var $request WindCommandRequest */
		$_SERVER['argv'] = isset($params[$this->paramKey]) ? $params[$this->paramKey] : array();
		isset($params[$this->moduleKey]) && $this->setModule($params[$this->moduleKey]);
		isset($params[$this->controllerKey]) && $this->setController($params[$this->controllerKey]);
		isset($params[$this->actionKey]) && $this->setAction($params[$this->actionKey]);
	}

	/**
	 * 是否是请求帮助
	 *
	 * @return boolean
	 */
	public function isHelp() {
		return $this->help;
	}

	/**
	 * 返回当前命令
	 *
	 * @return string
	 */
	public function getCmd() {
		return $this->cmd;
	}

	/**
	 * @return string
	 */
	public function getParamKey() {
		return $this->paramKey;
	}
}

?>