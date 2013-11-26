<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
/**
 * hook标签解析
 * 
 * 示例：
 * <code>
 * class MyClass {
 * public function plus($a, $b) {
 * echo $a + $b;
 * }
 * 
 * public static function myStatic() {
 * echo 'static';
 * }
 * }
 * $myclass = new MyClass();
 * 1、调用类中的方法可使用<hook class="$myClass" method="plus" args = "array(1,2)" alias='alias'/>
 * 2、调用类静态方法可使用<hook class="MyClass" method="mystatic" args = "array()" alias='alias'/>
 * 或<hook method="myClass::mystatic" args="array()" alias='alias'/>
 * 3、调用模板中的hook： <hook name="hookName" method="runDo" args = "array()" alias='alias'/> //runDo方法时默认调用的方法
 * 4、调用全局function可使用<hook method="func" args="array()" alias='alias'/>
 * </code>
 * 
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwTemplateCompilerHook.php 24895 2013-02-25 10:36:16Z jieyin $
 * @package wekit
 * @subpackage engine.extension.viewer
 */
class PwTemplateCompilerHook extends AbstractWindTemplateCompiler {
	
	/**
	 * 调用的类名
	 */
	protected $class;
	
	/**
	 * 调用的方法名
	 */
	protected $method;
	
	/**
	 * @var string|array
	 */
	protected $args;
	
	/**
	 * 该钩子下所有钩子片段集中缓存的位置
	 * @var string
	 */
	protected $alias = '';
	
	/**
	 * 钩子名称
	 *
	 * @var string
	 */
	protected $name = 'hook';
	
	/**
	 * 是否需要显示页面提示，默认为true
	 */
	protected $display;

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		$content = array();
		$content[] = '<?php';
		if (Wekit::load('APPCENTER:service.srv.PwDebugApplication')->inDevMode2()) {
			$_content = $this->_devHook();
			$content[] = 'echo \'' . $_content . '\';';
		}
		if (!$this->args) {
			$this->args = '';
		} else {
			$this->args = preg_replace(array('/\s*array\s*\(\s*/i', '/\s*\)\s*$/i'), '', $this->args);
		}
		$this->method = $this->method ? $this->method : 'runDo';
		if ($this->class) {
			$this->args = "'" . $this->name . "'" . ($this->args ? "," . $this->args : '');
			$callback = 'array(' . $this->class . ', "' . $this->method . '")';
		} elseif ($this->name) {
			$callback = 'array(PwSimpleHook::getInstance("' . $this->name . '"), "' . $this->method . '")';
		} else {
			$callback = '"' . $this->method . '"';
		}
		$this->args = 'array(' . $this->args . ')';
		$this->alias = trim($this->alias);
		$content[] = 'PwHook::display(' . $callback . ', ' . $this->args . ', "' . $this->alias . '", $__viewer);';
		$content[] = '?>';
		return implode("\r\n", $content);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::preCompile()
	 */
	public function preCompile() {
		$this->class = $this->method = $this->args = $this->name = '';
	}

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::getProperties()
	 */
	public function getProperties() {
		return array('class', 'method', 'args', 'name', 'alias', 'display');
	}
	
	private function _devHook() {
		if ($this->display && $this->display == 'false') {
			return '';
		}
		if ($this->class) {
			$_hookkey = '\',' . $this->class . '->getHookKey(),\'';
			$_content = $_hookkey . '.' . $this->name;
		} else {
			$_content = $_hookkey = 's_' . $this->name;
		}
		return '<a href="http://wiki.open.phpwind.com/index.php?title=HOOK#' . $_hookkey . '" class="icon_hooktip" target="_blank"><span><em></em>' . $_content . '</span></a>';
	}
}
?>