<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
/**
 * 批量加载模板片段标签解析
 * <code>
 * <!--#foreach ($templateList as $key => $tmp) {#-->
 * 		<segment tpl='$tmp' args='array()' alias='batchForeach'/>
 * <!--#}#-->
 * </code>
 * 
 * 如上将会batchtemp加载的模板都编译到batchForeach缓存文件中保存。
 * 
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwTemplateCompilerSegment.php 7486 2012-04-06 09:30:48Z xiaoxia.xuxx $
 * @package wekit
 * @subpackage engine.extension.viewer
 */
class PwTemplateCompilerSegment extends AbstractWindTemplateCompiler {
	protected $alias = ''; //别名，保存的编译文件名
	protected $tpl = ''; //模板文件
	protected $args = ''; //传递给模板的参数
	protected $name = '';//调用的模板片段中的方法
	
	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		if (!$this->tpl) return $content;
		if (0 !== strpos($this->args, 'array')) {
			$this->args = 'array(' . $this->args . ')';
		}
		$this->args || $this->args = '""';
		$content = array();
		$content[] = '<?php';
		$content[] = 'PwHook::segment("' . $this->tpl . '", ' . $this->args . ', "' . $this->name . '", "' . $this->alias . '", $__viewer);';
		$content[] = '?>';
		return implode(" ", $content);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::getProperties()
	 */
	public function getProperties() {
		return array('tpl', 'alias', 'args', 'name');
	}
}