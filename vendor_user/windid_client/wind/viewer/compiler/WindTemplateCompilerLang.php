<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
/**
 * 输出翻译后的语言信息
 * <code>
 * <lang message = '' args = 'array()'/>
 * </code>
 * 
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindTemplateCompilerLang.php 3791 2012-10-30 04:01:29Z liusanbian $
 * @package viewer
 * @subpackage compiler
 */
class WindTemplateCompilerLang extends AbstractWindTemplateCompiler {
	protected $message = '';
	protected $params = '';
	
	/*
	 * (non-PHPdoc) @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		if (!$this->message) return $content;
		$resource = Wind::getComponent('i18n');
		$resource !== null && $this->message = $resource->getMessage($this->message);
		if (!$this->params) return $this->message;
		return '<?php echo WindUtility::strtr("' . $this->message . '", ' . $this->params . ');?>';
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindTemplateCompiler::getProperties()
	 */
	protected function getProperties() {
		return array('message', 'params');
	}
}
