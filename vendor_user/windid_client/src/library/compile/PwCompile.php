<?php
/**
 * 开发者模式,动态编译器
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-12-23
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwCompile.php 7049 2012-03-29 03:20:38Z liusanbian $
 * @package wekit
 * @subpackage compile
 */
class PwCompile extends WindHandlerInterceptor {

	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::preHandle()
	 */
	public function preHandle() {
		if (!$compilers = $this->getCompiler()) return;
		/* @var $chain WindHandlerInterceptorChain */
		$chain = WindFactory::createInstance('WindHandlerInterceptorChain');
		foreach ($compilers as $key => $value) {
			$_compiler = Wind::import($value);
			$chain->addInterceptors(array($key => new $_compiler()));
		}
		$chain->getHandler()->handle();
	}

	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::postHandle()
	 */
	public function postHandle() {}

	/**
	 * 返回当前注册的编译器
	 */
	protected function getCompiler() {
		return array('css' => 'LIB:compile.compiler.PwCssCompiler');
	}
}

?>