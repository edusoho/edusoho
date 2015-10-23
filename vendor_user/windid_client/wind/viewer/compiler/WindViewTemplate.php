<?php
Wind::import('WIND:viewer.AbstractWindViewTemplate');
Wind::import('WIND:utility.WindFile');
/**
 * 模板编译类
 * 
 * 职责：进行模板编译,该类接收一个需要编译的模板文件地址,获取该模板内容并进行编译处理然后返回该内容.
 * 组件配置信息,可以通过config标签实现标签扩展支持:<code>
 * 'template' => array(
 * 'path' => 'WIND:viewer.compiler.WindViewTemplate',
 * 'scope' => 'prototype',
 * 'config' => array('resource' => ''),
 * )
 * <config>
 * //配置信息
 * <support-tags>
 * <!-- 标签配置：name: 标签名字， tag:具体的标签， pattern:匹配表达式 ，  compiler:解析类文件-->
 * <!--<tag name='' tag='' pattern='' compiler='' /> -->
 * </support-tags>
 * </config>
 * </code>
 * 扩展名为'test'的标签扩展配置如下,需要定义标签名称,用于匹配该标签的正则表达式(可以为空,为空则调用默认的正则标签匹配,通常情况下请保持为空),
 * 以及用于解析该标签的标签编译器(标签编译器需要继承AbstractWindTemplateCompiler):<code>
 * <support-tags>
 * <tag name='test' tag='test' pattern='<(test)[^<>\n]*(\/>|>[^<>]*<\/\1>)' compiler='TEST:TestTemplateCompiler' />
 * </support-tags>
 * </code>
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindViewTemplate.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package viewer
 * @subpackage compiler
 */
class WindViewTemplate extends AbstractWindViewTemplate {
	/**
	 * match后的模块文件块
	 *
	 * @var array
	 */
	protected $compiledBlockData = array();
	/**
	 * @var WindHandlerInterceptorChain
	 */
	protected $windHandlerInterceptorChain = null;
	protected $_compilerCache = array();

	/* (non-PHPdoc)
	 * @see AbstractWindViewTemplate::doCompile()
	 */
	protected function doCompile($content, $windViewerResolver = null) {
		try {
			$content = $this->registerTags($content, $windViewerResolver);
			if ($this->windHandlerInterceptorChain !== null) {
				$this->windHandlerInterceptorChain->getHandler()->handle();
			}
			foreach (array_reverse($this->compiledBlockData) as $key => $value) {
				if (!$key) continue;
				$content = str_replace($this->getBlockTag($key), ($value ? $value : ' '), $content);
			}
			$content = preg_replace('/\?>(\s|\n)*?<\?php/i', "\r\n", $content);
			return $content;
		} catch (Exception $e) {
			throw new WindViewException(
				'[viewer.WindViewTemplate.doCompile] compile fail.' . $e->getMessage(), 
				WindViewException::ERROR_SYSTEM_ERROR);
		}
	}

	/**
	 * 注册支持的标签并返回注册后的模板内容
	 * 
	 * 解析模板内容,并将匹配到的标签内容块注册到相应的标签编译器中并初始化标签解析器处理链.
	 * @param string $content
	 * @param WindViewerResolver $windViewerResolver
	 * @return string 
	 */
	private function registerTags($content, $windViewerResolver = null) {
		foreach ((array) $this->getTags() as $key => $value) {
			$compiler = isset($value['compiler']) ? $value['compiler'] : '';
			$regex = isset($value['pattern']) ? $value['pattern'] : '';
			$tag = isset($value['tag']) ? $value['tag'] : '';
			if (!$compiler || !$tag) continue;
			if ($regex === '') $regex = '/<(' . preg_quote($tag) . ')(.*?)(\/>|>(.*?)<\/\1>)/is';
			$content = $this->creatTagCompiler($content, $compiler, $regex, $windViewerResolver);
		}
		return $content;
	}

	/**
	 * 创建对应标签的解析器类实例对象,并加载到处理链中.
	 * 
	 * @param string content 模板内容
	 * @param string compiler 标签编译器
	 * @param string regex 正则表达式
	 * @param WindViewerResolver $windViewerResolver 默认为null
	 * @return string 返回处理后的模板内容
	 */
	private function creatTagCompiler($content, $compiler, $regex, $windViewerResolver = null) {
		$content = preg_replace_callback($regex, array($this, '_creatTagCompiler'), $content);
		if ($this->windHandlerInterceptorChain === null) {
			$this->windHandlerInterceptorChain = new WindHandlerInterceptorChain();
		}
		$_compilerClass = Wind::import($compiler);
		$this->windHandlerInterceptorChain->addInterceptors(
			new $_compilerClass($this->_compilerCache, $this, $windViewerResolver, 
				$this->getRequest(), $this->getResponse()));
		$this->_compilerCache = array();
		return $content;
	}

	/**
	 * 返回当前解析器中所有注册进来的标签集合
	 * 
	 * @return array
	 */
	protected function getTags() {
		$_tags['internal'] = $this->createTag('internal', 'WIND:viewer.compiler.WindTemplateCompilerInternal', 
			'/<\?php.*?\?>/is');
		/*标签体增加在该位置*/
		$_tags['template'] = $this->createTag('template', 
			'WIND:viewer.compiler.WindTemplateCompilerTemplate');
		//$_tags['page'] = $this->createTag('page', 'WIND:viewer.compiler.WindTemplateCompilerPage');
		$_tags['action'] = $this->createTag('action', 
			'WIND:viewer.compiler.WindTemplateCompilerAction');
		//$_tags['component'] = $this->createTag('component', 'WIND:viewer.compiler.WindTemplateCompilerComponent');
		$_tags['token'] = $this->createTag('token', 
			'WIND:viewer.compiler.WindTemplateCompilerToken');
		$_tags['lang'] = $this->createTag('lang', 'WIND:viewer.compiler.WindTemplateCompilerLang');
		$_tags = array_merge($_tags, $this->getConfig('support-tags', '', array()));
		/*标签解析结束*/
		$_tags['expression'] = $this->createTag('expression', 
			'WIND:viewer.compiler.WindTemplateCompilerEcho', '/({@|{\$[\w$]{1})[^}{@\n]*}/i');
		
		$_tags = array_merge($_tags, $this->getConfig('support-tags-end', '', array()));
		//$_tags['echo'] = $this->createTag('echo', 'WIND:viewer.compiler.WindTemplateCompilerEcho', '/\$[\w_]+/i');
		/* 块编译标签，嵌套变量处理 */
		//$_tags['script1'] = $this->createTag('script1', 'WIND:viewer.compiler.WindTemplateCompilerScript', '/<!--\[[\w\s]*\]>(.|\n)*<!\[[\w\s]*\]-->/Ui');
		//$_tags['script'] = $this->createTag('script', 'WIND:viewer.compiler.WindTemplateCompilerScript', '/<(script)[^<>\n]*(\/>|>(.|\n)*<\/\1>)/Ui');
		//$_tags['link'] = $this->createTag('link', 'WIND:viewer.compiler.WindTemplateCompilerCss');
		//$_tags['style'] = $this->createTag('style', 'WIND:viewer.compiler.WindTemplateCompilerCss');
		return $_tags;
	}

	/**
	 * 创建tag编译解析的配置
	 * 
	 * @param string $tag 标签名称
	 * @param string $class 编译器类新型
	 * @param stirng $pattern 正则表达式,用于匹配标签 默认为空
	 * @return array
	 */
	private function createTag($tag, $class, $pattern = '') {
		return array('tag' => $tag, 'pattern' => $pattern, 'compiler' => $class);
	}

	/**
	 * 将标签匹配到的模板内容设置到缓存中，并返回标识位设置到模板中进行内容占为
	 * 
	 * @param string $content
	 * @return mixed
	 */
	private function _creatTagCompiler($content) {
		$_content = $content[0];
		if (!$_content) return '';
		
		$key = $this->getCompiledBlockKey();
		$this->_compilerCache[] = array($key, $_content);
		return $this->getBlockTag($key);
	}

	/**
	 * 对模板块存储进行标签处理
	 * 将Key串 'HhQWFLtU0LSA3nLPLHHXMtTP3EfMtN3FsxLOR1nfYC5OiZTQri' 处理为
	 * <pw-wind key='HhQWFLtU0LSA3nLPLHHXMtTP3EfMtN3FsxLOR1nfYC5OiZTQri' />
	 * 在模板中进行位置标识
	 * 
	 * @param string $key 索引
	 * @return string|mixed 处理后结果
	 */
	private function getBlockTag($key) {
		return '#' . $key . '#';
	}

	/**
	 * 获得切分后块编译缓存Key值,Key值为一个50位的随机字符串,当产生重复串时继续查找
	 * 
	 * @return string
	 */
	protected function getCompiledBlockKey() {
		$key = WindUtility::generateRandStr(50);
		if (key_exists($key, $this->compiledBlockData)) {
			return $this->getCompiledBlockKey();
		}
		return $key;
	}

	/**
	 * 返回编译后结果，根据Key值检索编译后结果，并返回.当key值为空时返回全部数据
	 * 
	 * @param string $key
	 * @return string|array
	 */
	public function getCompiledBlockData($key = '') {
		if ($key)
			return isset($this->compiledBlockData[$key]) ? $this->compiledBlockData[$key] : '';
		else
			return $this->compiledBlockData;
	}

	/**
	 * 根据key值保存编译后的模板块
	 * 
	 * @param string $key 索引
	 * @param string $compiledBlockData 编译结果
	 * @return void
	 */
	public function setCompiledBlockData($key, $compiledBlockData) {
		if ($key) $this->compiledBlockData[$key] = $compiledBlockData;
	}

}

?>