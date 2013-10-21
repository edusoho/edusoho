<?php
Wind::import('LIB:route.AbstractPwRoute');
/**
 * 普通前台路由，供后台框架使用
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwCommonRoute.php 25189 2013-03-06 10:28:01Z jieyin $
 * @package library
*/
class PwCommonRoute extends AbstractPwRoute {
	/* (non-PHPdoc)
	 * @see AbstractWindRoute::build()
	 */
	public function build($router, $action, $args = array()) {
		list($_a, $_c, $_m, $args) = WindUrlHelper::resolveAction($action, $args);
		if ($_m && $_m !== $router->getDefaultModule()) $args[$router->getModuleKey()] = $_m;
		if ($_c && $_c !== $router->getDefaultController()) $args[$router->getControllerKey()] = $_c;
		if ($_a && $_a !== $router->getDefaultAction()) $args[$router->getActionKey()] = $_a;
		$_url = 'index.php?' . WindUrlHelper::argsToUrl($args);
		return $_url;
	}

	/* (non-PHPdoc)
	 * @see AbstractWindRoute::match()
	 */
	 public function match($request) {
	 	$path = $request->getPathInfo();
		return WindUrlHelper::urlToArgs($path);
	}
}
?>