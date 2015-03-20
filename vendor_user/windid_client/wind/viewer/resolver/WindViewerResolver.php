<?php
Wind::import('WIND:viewer.resolver.WindNormalViewerResolver');
Wind::import('WIND:viewer.exception.WindViewException');
/**
 * 视图渲染器引擎
 * 该类实现了接口<i>IWindViewerResolver</i>,主要职责是进行视图渲染，并返回渲染的视图内容.
 * 支持布局管理，主题管理以及通过<i>WindViewTemplate</i>支持视图模板编译。
 * 组件定义:<code>
 * 'viewResolver' => array(
 * 'path' => 'WIND:viewer.WindViewerResolver',
 * 'scope' => 'prototype',
 * 'properties' => array(
 * 'windLayout' => array(
 * 'ref' => 'layout',
 * )))</code>
 * <note><b>注意:</b>框架默认视图渲染引擎组件,可以通过覆盖component相关配置进行修改</note>
 * <note>WindView和WindViewerResolver是相互配合使用的,等WindView接受一个视图渲染请求后会初始化一个ViewerResolver对象并将进一步的视图渲染工作移交给该对象.
 * 而ViewerResolver对象在进行视图渲染时的状态信息，模板信息，以及配置信息都来自于WindView对象.ViewerResolver对象中的WindView对象必须是创建ViewerResolver的那个对象.
 * 我们可以通过修改view的component配置来注入不同的ViewerResolver实现.
 * </note>
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindViewerResolver.php 3908 2013-01-18 03:37:53Z yishuo $
 * @package viewer
 */
class WindViewerResolver extends WindNormalViewerResolver implements IWindViewerResolver {
	private $currentThemeKey = null;
	
	/*
	 * (non-PHPdoc) @see IWindViewerResolver::windFetch()
	 */
	public function windFetch($template = '') {
		$template || $template = $this->windView->templateName;
		if (!$template) return '';
		list($compileFilePath) = $this->compile($template);
		WindRender::render($compileFilePath, $this->vars, $this);
	}

	/**
	 * 编译模板并返回编译后模板地址及内容
	 * <pre>
	 * <i>$output==true</i>返回编译文件绝对路径地址和内容,不生成编译文件;
	 * <i>$output==false</i>返回编译文件绝对路径地址和内容,生成编译文件
	 * </pre>
	 * 
	 * @param string $template 模板名称 必填
	 * @param string $suffix 模板后缀 默认为空
	 * @param boolean $readOnly 是否直接输出模板内容,接受两个值true,false 默认值为false
	 * @param boolean $forceOutput 是否强制返回模板内容,默认为不强制
	 * @return array(compileFile,content) <pre>
	 *         <i>compileFile</i>模板编译文件绝对地址,
	 *         <i>content</i>编译后模板输出内容,当<i>$output</i>
	 *         为false时将content写入compileFile</pre>
	 */
	public function compile($template, $suffix = '', $readOnly = false, $forceOutput = false) {
		list($templateFile, $compileFile, $this->currentThemeKey) = $this->windView->getViewTemplate(
			$template, $suffix);
		if (!is_file($templateFile)) {
			throw new WindViewException(
				'[viewer.resolver.WindViewerResolver.compile] ' . $templateFile, 
				WindViewException::VIEW_NOT_EXIST);
		}
		
		if (!$this->checkReCompile($templateFile, $compileFile)) {
			return array(
				$compileFile, 
				($forceOutput || $readOnly ? WindFile::read($compileFile) : ''));
		}
		/* @var $_windTemplate WindViewTemplate */
		$_windTemplate = Wind::getComponent('template');
		$_output = $_windTemplate->compile($templateFile, $this);
		if (false === $readOnly) {
			WindFolder::mkRecur(dirname($compileFile));
			WindFile::write($compileFile, $_output);
		}
		return array($compileFile, $_output);
	}

	/**
	 * 如果存在模板风格，该方法将返回当前风格值
	 * 
	 * @return NULL int
	 */
	public function getCurrentThemeKey() {
		return $this->currentThemeKey;
	}

	/**
	 * 检查是否需要重新编译,需要编译返回false，不需要编译返回true
	 * 是否重新进行编译取决于两个变量'Wind::$isDebug'和'isCompile','Wind::$isDebug'是框架层面的'DEBUG'控制常量,当'DEBUG'开启时则总是重新生成编译模板.
	 * 'isCompile'是一个配置值来自'WindView'对象,用户可以通过配置进行修改.当'isCompile'为'1'时,程序会进一步判断,当编译文件不存在或者已经过期时对模板进行重新编译.
	 * 如果'isCompile'为'0',则不对模板文件进行重新编译.
	 * 
	 * @param string $templateFilePath 模板路径
	 * @param string $compileFilePath 编译路径
	 * @return boolean
	 */
	private function checkReCompile($templateFilePath, $compileFilePath) {
		if (Wind::$isDebug) return true;
		if ($this->getWindView()->isCompile) {
			if (!is_file($compileFilePath)) return true;
			$_c_m_t = filemtime($compileFilePath);
			if ((int) $_c_m_t <= (int) filemtime($templateFilePath)) return true;
		}
		return false;
	}
}
