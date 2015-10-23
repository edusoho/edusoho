<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
/**
 * 组件标签编译器
 * 
 * 标签使用例子:
 * <code>
 * <component name="test" args="" tpl="template" /></code>
 * @author xiaoxiao <x_824@sina.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwTemplateCompilerComponent.php 22295 2012-12-21 05:39:44Z gao.wanggao $
 * @package wekit
 * @subpackage engine.extension.viewer
 */
class PwTemplateCompilerComponent extends AbstractWindTemplateCompiler {
	protected $name = '';
	
	protected $tpl = '';
	protected $suffix = '';
	protected $args = 'array()';
	
	protected $method = '';
	protected $class;
	protected $action;

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		if ($this->name) {
			if (!$this->_config) $this->setConfig(Wind::getRealPath('CONF:pwcomponents.php', true));
			if ($config = $this->getConfig($this->name)) {
				$this->class || !isset($config['class']) || $this->class = $config['class'];
				$this->method || !isset($config['method']) || $this->method = $config['method'];
				$this->tpl || !isset($config['tpl']) || $this->tpl = $config['tpl'];
				$this->suffix || !isset($config['suffix']) || $this->suffix = $config['suffix'];
				$this->action || !isset($config['action']) || $this->action = $config['action'];
			}
		}
		
		if (strpos($this->args, '$') === false && strpos(strtolower($this->args), 'array') === false) {
			$this->args = '\'' . $this->args . '\'';
		}
		$content = '<?php ';
		
		if ($this->action) {
			//TODO ajax 调用兼容
			$content .= '<?php $_tpl_forward = Wind::getComponent(\'forward\');';
			if (strpos($this->action, '$') === false) $this->action = '\'' . $this->action . '\'';
			$content .= '$_tpl_forward->forwardAction(' . $this->action . ',' . $this->args . ',false,false);';
			$content .= 'Wind::getApp()->doDispatch($_tpl_forward, true); ?>';
		} else {
			if ($this->class) {
				$content .= '$__tpl_data = call_user_func_array(
								array(Wekit::load("' . $this->class . '"), 
								"' . $this->method . '"), 
								array(' . $this->args . '));';
			} else {
				$content .= '$__tpl_data = ' . $this->args . ';';
			}
			if ($this->tpl) {
				list(, $_content) = $this->windViewerResolver->compile($this->tpl, $this->suffix, true);
				$content .= ' ?>' . $_content . '<?php ';
			}
		}
		return $content . ' ?>';
	}

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::getProperties()
	 */
	protected function getProperties() {
		return array('name', 'tpl', 'args', 'class', 'method', 'suffix');
	}
}

?>