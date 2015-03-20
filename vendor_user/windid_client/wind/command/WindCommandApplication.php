<?php
/**
 * 命令行应用控制器
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindCommandApplication.php 3859 2012-12-18 09:25:51Z yishuo $
 * @package command
 */
class WindCommandApplication extends AbstractWindApplication {

	/**
	 * 显示帮助信息
	 *
	 * @param string $className        	
	 * @param WindException $e        	
	 */
	protected function help($className, $e = null) {
		$helps = array();
		$helps[10] = 'usage: command [options] [args]';
		$helps[11] = 'Valid options:';
		$helps[12] = $this->handlerAdapter->getModuleKey() . ' 		routing information,the name of application module';
		$helps[13] = $this->handlerAdapter->getControllerKey() . ' 	routing information,the name of controller';
		$helps[14] = $this->handlerAdapter->getActionKey() . ' 		routing information,the name of action';
		$helps[15] = $this->handlerAdapter->getParamKey() . '		the parameters of the method [action]';
		if (class_exists($className)) {
			/* @var $handler WindCommandController */
			$handler = new $className();
			$action = $this->handlerAdapter->getAction();
			if ($action !== 'run') $action = $handler->resolvedActionName(
				$this->handlerAdapter->getAction());
			if (!method_exists($handler, $action)) return;
			$method = new ReflectionMethod($handler, $action);
			$helps[20] = "\r\nlist -p [paraments] of '$className::$action' \r\n";
			$method = $method->getParameters();
			$i = 21;
			foreach ($method as $value) {
				$helps[$i++] = $value;
			}
		}
		if ($e !== null) $helps[0] = $e->getMessage() . "\r\n";
		exit(implode("\r\n", $helps));
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindApplication::doDispatch()
	 */
	public function doDispatch($forward) {
		// TODO Auto-generated method stub
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindApplication::sendErrorMessage()
	 */
	protected function sendErrorMessage($errorMessage, $errorcode) {
		// TODO Auto-generated method stub
	}
}

?>