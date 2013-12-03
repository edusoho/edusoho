<?php
/**
 * 请求分发重定向处理类
 * 
 * 通过该类进行请求的分发以及重定向,请求分发有三种分发类型:'重定向类型','重定向到新的action处理操作','视图渲染'.
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindDispatcher.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package web
 */
class WindDispatcher extends WindModule {
	/**
	 * 存储请求的队列信息
	 * 
	 * @var array
	 */
	protected $maxForwrd = array();
	protected $display = false;

	/**
	 * @param WindForward $forward
	 * @param WindRouter $router
	 * @param boolean $display
	 * @return void
	 */
	public function dispatch($forward, $router, $display) {
		if ($forward->getIsRedirect())
			$this->dispatchWithRedirect($forward, $router);
		elseif ($forward->getIsReAction()) {
			if (count($this->maxForwrd) > 10) {
				throw new WindFinalException(
					'[web.WindDispatcher.dispatch] more than 10 times forward request. (' . implode(', ', 
						$this->maxForwrd) . ')');
			}
			$token = $router->getModule() . '/' . $router->getController() . '/' . $router->getAction();
			array_push($this->maxForwrd, $token);
			$this->dispatchWithAction($forward, $router, $display);
		} else {
			$view = $forward->getWindView();
			if ($view->templateName) {
				$this->getResponse()->setData($forward->getVars(), $view->templateName);
				$view->render($this->display || $display);
			}
			$this->display = false;
		}
	}

	/**
	 * 重定向请求到新的url地址
	 * 
	 * 重定向请求到新的url地址是通过head方式重新开启一个url访问请求.
	 * @param WindForward $forward
	 * @param AbstractWindRouter $router
	 * @return void
	 */
	protected function dispatchWithRedirect($forward, $router) {
		if (!($_url = $forward->getUrl())) {
			$_url = $router->assemble($forward->getAction(), $forward->getArgs());
		}
		$_url = WindUrlHelper::checkUrl($_url, true);
		$this->getResponse()->sendRedirect($_url);
	}

	/**
	 * 重定向请求到新的action操作
	 * 
	 * 该种重定向类型,是中断当前的请求执行过程,开启另外的action操作处理.是在一次请求内部进行重定向,
	 * 所以之前的一些处理的结果变量,在重定向后是会继续存在,并可通过forward变量进行访问的.也就是不仅仅是过程的重定向,
	 * 也是状态的重定向.
	 * @param WindForward $forward
	 * @param WindRouter $router
	 * @param boolean $display
	 * @return void
	 */
	protected function dispatchWithAction($forward, $router, $display) {
		if (!$action = $forward->getAction()) {
			throw new WindException('[web.WindDispatcher.dispatchWithAction] forward fail.', 
				WindException::ERROR_PARAMETER_TYPE_ERROR);
		}
		$this->display = $display;
		list($_a, $_c, $_m, $arg) = WindUrlHelper::resolveAction($action);
		foreach ($arg as $key => $value) {
			$_GET[$key] = $value;
		}
		foreach ($forward->getArgs() as $key => $value) {
			$_POST[$key] = $value;
		}
		
		$_a && $router->setAction($_a);
		$_c && $router->setController($_c);
		$_m && $router->setModule($_m);
		Wind::getApp()->run();
	}
}
