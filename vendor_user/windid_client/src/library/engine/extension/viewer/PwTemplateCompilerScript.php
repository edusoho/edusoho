<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
/**
 * 编译javascript标签
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwTemplateCompilerScript.php 2128 2011-11-07 08:57:28Z xiaoxia.xuxx $
 * @package wekit
 * @subpackage engine.extension.viewer
 */
class PwTemplateCompilerScript extends AbstractWindTemplateCompiler {
	protected $compile = 'true';

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
 		if ($this->compile === 'false') return $content;
		foreach ($this->windViewTemplate->getCompiledBlockData() as $key => $value) {
			$content = str_replace('#' . $key . '#', ($value ? $value : ' '), $content);
		}
		$this->windViewerResolver->getWindLayout()->setScript($content);
		return '';
	}

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::getProperties()
	 */
	protected function getProperties() {
		return array('compile');
	}

}

?>