<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
/**
 * 输出安全令牌隐藏域
 * 
 * <token name='' />
 * @author Qiong Wu <papa0924@gmail.com> 2011-10-19
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindTemplateCompilerToken.php 3791 2012-10-30 04:01:29Z liusanbian $
 * @package viewer
 * @subpackage compiler
 */
class WindTemplateCompilerToken extends AbstractWindTemplateCompiler {
	protected $name = '';

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		$_content = '<?php $__tpl_token = Wind::getComponent(\'windToken\');';
		$_content .= '$__tpl_token_value = $__tpl_token->saveToken(\'' . $this->name . '\'); ?>';
		$_content .= '<input type=\'hidden\' name=\'' . $this->name . '\'  value=\'<?php echo $__tpl_token_value ?>\'/>';
		return $_content;
	}

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::getProperties()
	 */
	protected function getProperties() {
		return array('name');
	}

}

?>