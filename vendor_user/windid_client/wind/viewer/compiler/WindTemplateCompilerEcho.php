<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
Wind::import('WIND:utility.WindSecurity');
/**
 * 变量输出编译类
 * 变量输出格式:<code>
 * 变量名称|变量格式（html，text）
 * {$var|html} //不执行编译
 * {@$var->a|text} //执行编译
 * {@templateName:var|html}</code>
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindTemplateCompilerEcho.php 3773 2012-10-19 09:53:40Z long.shi
 *          $
 * @package viewer
 * @subpackage compiler
 */
class WindTemplateCompilerEcho extends AbstractWindTemplateCompiler {
	
	/*
	 * (non-PHPdoc) @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		preg_match('/^({@|{)([^}{@\n]*);?(\|(\w+))?}$/iU', $content, $matchs);
		if (empty($matchs[2])) return $content;
		$_type = isset($matchs[4]) ? $matchs[4] : '';
		$_output = $matchs[2];
		preg_match('/^([\w_]+):([\w\s\._]+)$/i', $_output, $matchs);
		if ($matchs) {
			$_args = explode('.', $matchs[2] . '.');
			$_output = 'Wind::getComponent(\'response\')->getData(\'' . $matchs[1] . '\'';
			foreach ($_args as $_arg) {
				$_arg && $_output .= ',\'' . $_arg . '\'';
			}
			$_output .= ')';
		}
		
		$charset = Wind::getComponent('response')->getCharset();
		switch (strtolower($_type)) {
			case 'json':
		/*		$content = '<?php echo WindJson::encode(' . $_output . ', \'' . $charset . '\');?>';   */
				$content = '<?php echo WindSecurity::escapeEncodeJson(' . $_output . ', \'' . $charset . '\');?>';
				break;
			case 'html':
			case 'js':
				$content = '<?php echo ' . $_output . ';?>';
				break;
			case 'text':
				$content = '<?php echo strip_tags(' . $_output . ');?>';
				break;
			case 'url':
			default:
				$charset == 'GBK' && $charset = 'ISO-8859-1';
				$content = '<?php echo htmlspecialchars(' . $_output . ', ENT_QUOTES, \'' . $charset . '\');?>';
		}
		return $content;
	}
}
?>