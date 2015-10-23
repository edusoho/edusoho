<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
/**
 * <doAction /> 标签解析脚本
 * 支持属性:
 * action: 处理操作,支持格式 /module/controller/action/?args
 * args: 参数列表
 * isRedirect: 是否用url重定向模式进行跳转
 * <code>
 * <action action='run' args='{$a}' isRedirect='true' />
 * </code>
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindTemplateCompilerAction.php 2917 2011-10-09 02:48:19Z
 *          xiaoxia.xuxx $
 * @package viewer
 * @subpackage compiler
 */
class WindTemplateCompilerAction extends AbstractWindTemplateCompiler {
	protected $action = '';
	protected $args = array();
	protected $isRedirect = 'false';
	
	/*
	 * (non-PHPdoc) @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		$content = '<?php $_tpl_forward = Wind::getComponent(\'forward\');';
		!$this->args && $this->args = 'array()';
		if (!preg_match('/^{?\$(\w+)}?$/Ui', $this->action, $_tmp)) $this->action = '\'' . $this->action . '\'';
		$content .= '$_tpl_forward->forwardAction(' . $this->action . ',' . $this->args . ',' . $this->isRedirect . ',false);';
		$content .= 'Wind::getApp()->doDispatch($_tpl_forward, true); ?>';
		return $content;
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindTemplateCompiler::getProperties()
	 */
	public function getProperties() {
		return array('action', 'args', 'isRedirect');
	}
}