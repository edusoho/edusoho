<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');

/**
 * 
 * 在FORM表单中统一加入Token,由PwCsrfFilter.php进行统一提交验证
 *
 * @author liusanbian <liusanbian@aliyun.com> 
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package wekit
 * @subpackage engine.extension.viewer
 */
class PwTemplateCompilerCsrftoken extends AbstractWindTemplateCompiler {
	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::compile()
	*/
	public function compile($key, $content) {
		$_content = '<input type="hidden" name="csrf_token" value="<?php echo WindSecurity::escapeHTML(Wind::getComponent(\'windToken\')->saveToken(\'csrf_token\')); ?>"/></form>';
		return $_content;
	}
}

?>