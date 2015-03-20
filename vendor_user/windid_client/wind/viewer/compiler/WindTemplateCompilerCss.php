<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
/**
 * css标签编译器
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindTemplateCompilerCss.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package viewer
 * @subpackage compiler
 */
class WindTemplateCompilerCss extends AbstractWindTemplateCompiler {

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		foreach ($this->windViewTemplate->getCompiledBlockData() as $key => $value) {
			$content = str_replace('#' . $key . '#', ($value ? $value : ' '), $content);
		}
		$this->windViewerResolver->getWindLayout()->setcss($content);
		return '';
	}
}

?>