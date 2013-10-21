<?php
/**
 * 组件服务类
 * 
 * 组件服务类,解析并加载组件并启动组件服务
 * @author Qiong Wu <papa0924@gmail.com> 2011-11-21
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwComponent.php 20274 2012-10-25 07:49:56Z yishuo $
 * @package wekit
 * @subpackage engine.component
 */
class PwComponent extends WindModule {

	/**
	 * 组件调用接口
	 *
	 * @param string $name
	 * @param string $tpl
	 * @param array $args
	 * @return void
	 */
	public function component($name, $tpl = '', $args = array()) {
		if (!$_config = $this->getConfig($name)) return;
		if (!empty($_config['service'])) {
			$service = Wekit::load($_config['service']);
			!is_array($args) && $args = array($args);
			$method = empty($_config['method']) ? 'run' : $_config['method'];
			if (!method_exists($service, $method)) throw new PwException('method.not.exit', 
				array(
					'{parm1}' => 'wekit.engine.component.PwComponent.component', 
					'{parm2}' => $service, 
					'{parm3}' => $method));
			
			$result = call_user_func_array(array($service, $method), $args);
			$tpl || $tpl = $_config['template'];
			/* @var $forward WindForward */
			$forward = Wind::getComponent('forward');
			$forward->getWindView()->templateName = $tpl;
			$forward->setVars($result, 'data');
			Wind::getApp()->doDispatch($forward, true);
		} else
			die('sorry, not yet been realized');
	}

}

?>