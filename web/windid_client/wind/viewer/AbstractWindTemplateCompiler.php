<?php
/**
 * 模板标签解析器
 * 
 * 可以通过继承该抽象类,扩展模板的标签解析.在扩展模板的标签解析时实现'compile'方法即可.
 * 该方法接收一段match到的标签内容进行解析操作并返回解析后的结果.
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AbstractWindTemplateCompiler.php 3533 2012-05-08 08:24:20Z yishuo $
 * @package viewer
 */
abstract class AbstractWindTemplateCompiler extends WindHandlerInterceptor {
	/**
	 * @var array
	 */
	protected $tags= array();
	/**
	 * @var WindViewTemplate
	 */
	protected $windViewTemplate = null;
	/**
	 * @var WindViewerResolver
	 */
	protected $windViewerResolver = null;
	/**
	 * @var WindHttpRequest
	 */
	protected $request = null;
	/**
	 * @var WindHttpResponse
	 */
	protected $response = null;
	/**
	 * @var array
	 */
	protected $_propertiesCache = array();

	/**
	 * 初始化标签解析器
	 * 
	 * @param string $tags
	 * @param WindViewTemplate $windViewTemplate
	 * @param WindViewerResolver $windViewerResolver
	 * @param WindHttpRequest $request
	 * @param WindHttpResponse $response
	 */
	public function __construct($tags, $windViewTemplate, $windViewerResolver, $request, $response) {
		$this->tags = $tags;
		$this->windViewTemplate = $windViewTemplate;
		$this->windViewerResolver = $windViewerResolver;
		$this->request = $request;
		$this->response = $response;
	}

	/**
	 * 内容编译,接受一段落内容进行编译处理并返回编译内容
	 * 
	 * @param string $key 
	 * @param string $content 模板内容
	 * @return string 输出编译后结果
	 */
	abstract public function compile($key, $content);

	/**
	 * 编译前预处理
	 * 
	 * @return void
	 */
	protected function preCompile() {}

	/**
	 * 编译后处理结果
	 * 
	 * @return void
	 */
	protected function postCompile() {}

	/**
	 * 返回该标签支持的属性列表,需要覆盖该方法实现对标签的解析支持
	 * 
	 * @return array
	 */
	protected function getProperties() {
		return array();
	}

	/**
	 * 解析标签属性值
	 * 
	 * @param string $content
	 */
	protected function compileProperty($content) {
		foreach ($this->getProperties() as $value) {
			if (!$value || !property_exists($this, $value)) continue;
			if (!isset($this->_propertiesCache[$value])) {
				$this->_propertiesCache[$value] = $this->$value;
			}
			preg_match('/(?<=' . preg_quote($value) . '=([\'\"]))(.*?)(?=\1)/ie', $content, $result);
			$this->$value = $result ? $result[0] : $this->_propertiesCache[$value];
		}
	}

	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::preHandle()
	 */
	public function preHandle() {
		if ($this->windViewTemplate === null) return;
		$this->preCompile();
		foreach ($this->tags as $key => $value) {
			if (!$value[0] || !$value[1]) continue;
			$this->compileProperty($value[1]);
			$_output = $this->compile($value[0], $value[1]);
			$this->windViewTemplate->setCompiledBlockData($value[0], $_output);
		}
		$this->postCompile();
	}

	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::postHandle()
	 */
	public function postHandle() {}

	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::handle()
	 */
	public function handle() {
		$args = func_get_args();
		call_user_func_array(array($this, 'preHandle'), $args);
		if (null !== ($handler = $this->interceptorChain->getHandler())) {
			call_user_func_array(array($handler, 'handle'), $args);
		}
		call_user_func_array(array($this, 'postHandle'), $args);
	}

	/**
	 * @return WindViewTemplate
	 */
	protected function getWindViewTemplate() {
		return $this->windViewTemplate;
	}

}

?>