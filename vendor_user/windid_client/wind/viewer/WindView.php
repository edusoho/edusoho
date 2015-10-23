<?php
Wind::import('WIND:utility.WindFolder');
Wind::import('WIND:viewer.IWindView');
/**
 * 视图处理器
 * 
 * <i>WindView</i>是基础的视图处理器,职责：进行视图渲染.<br>
 * 他实现自接口<i>IWindView</i>,该类依赖<i>WindViewerResolver</i>完成视图渲染工作<br/>
 * <i>WindView</i>支持丰富的配置信息，可以通过修改相关配置来改变视图输出行为.<i>template-dir</i>
 * :模板目录,支持命名空间格式:WIND:template,当命名空间为空时以当前app的rootpath为默认命名空间;<i>template-ext</i>
 * :模板后缀,默认为htm,可以通过配置该值来改变模板的后缀名称;<i>is-compile</i>
 * :是否开启模板自动编译,当开启自动编译时程序会根据编译文件是否存在或者是否已经过期来判断是否需要进行重新编译.支持'0'和'1'两种输入,默认值为'0'.<i>compile-dir</i>
 * :模板编译目录,输入规则同'template-dir'.(注意:该目录需要可写权限).
 * 默认配置支持如下：<code> array(
 * 'template-dir' => 'template',
 * 'template-ext' => 'htm',
 * 'is-compile' => '0',
 * 'compile-dir' => 'DATA:template',
 * 'compile-ext' => 'tpl', //模板后缀
 * 'layout' => '', //布局文件
 * 'theme' => '', //主题包位置
 * 'htmlspecialchars' => 'true', //是否开启对输出模板变量进行过滤
 * )
 * </code>
 * 该类的组件配置格式：<code>
 * 'windView' => array('path' => 'WIND:viewer.WindView',
 * 'scope' => 'prototype',	//注意:命名空间为'prototype'
 * 'config' => array(
 * 'template-dir' => 'template',
 * 'template-ext' => 'htm',
 * 'is-compile' => '0',
 * 'compile-dir' => 'compile.template',
 * 'compile-ext' => 'tpl',
 * 'layout' => '',
 * 'theme' => ''),
 * 'properties' => array(
 * 'viewResolver' => array('ref' => 'viewResolver')
 * ))</code>
 * <note><b>注意:</b>框架默认视图组件,通过修改组件配置修改默认视图组件.(详细操作参考组件配置定义)</note>
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license {@link http://www.windframework.com}
 * @version $Id: WindView.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package viewer
 */
class WindView extends WindModule implements IWindView {
	/**
	 * 模板目录
	 * 
	 * 支持命名空间格式:<i>WIND:template</i>,
	 * 当命名空间为空时以当前<i>app</i>的<i>rootpath</i>为默认命名空间
	 * @var string
	 */
	public $templateDir;
	/**
	 * 模板文件的扩展名
	 * 
	 * @var string
	 */
	public $templateExt;
	/**
	 * 模板名称
	 * 
	 * @var string
	 */
	public $templateName;
	/**
	 * 是否对模板变量进行html字符过滤
	 * 
	 * @var boolean
	 */
	public $htmlspecialchars = true;
	/**
	 * 是否开启模板自动编译
	 * 
	 * 接受两种输入值0和1<ol>
	 * <li>0   关闭,不进行模板编译</li>
	 * <li>1  进行模板编译</li></ol>
	 * @var int
	 */
	public $isCompile = 0;
	/**
	 * 模板编译文件生成目录,目录定义规则同<i>templateDir</i>
	 * 
	 * @var string
	 */
	public $compileDir;
	/**
	 * 模板编译文件生成后缀,默认值为'tpl'
	 * 
	 * @var string
	 */
	public $compileExt = 'tpl';
	/**
	 * 模板布局文件
	 * 
	 * @var string
	 */
	public $layout;
	/**
	 * 更个包的结构
	 *
	 * @var string
	 */
	public $themePackPattern = '{pack}.{theme}';
	
	/**
	 * 主题包目录
	 * array('theme' => '', 'package' => '');
	 * @var string
	 */
	protected $theme = array();
	/**
	 * 视图解析引擎,通过组件配置改变该类型
	 * 
	 * @var WindViewerResolver
	 */
	protected $viewResolver = null;
	/**
	 * 视图布局管理器
	 *
	 * @var WindLayout
	 */
	protected $windLayout = null;
	
	/* (non-PHPdoc)
	 * @see IWindView::render()
	 */
	public function render($display = false) {
		if (!$this->templateName) return;
		
		/* @var $viewResolver WindViewerResolver */
		$viewResolver = $this->_getViewResolver();
		$viewResolver->setWindView($this);
		if ($viewResolver === null) throw new WindException(
			'[viewer.WindView.render] View renderer initialization failure.');
		$viewResolver->windAssign($this->getResponse()->getData($this->templateName));
		if ($display === false) {
			if ($this->layout) {
				/* @var $layout WindLayout */
				$layout = $this->_getWindLayout();
				$layout->parser($this->layout, $viewResolver);
			} else
				$viewResolver->windFetch();
		} else {
			$viewResolver->windFetch();
		}
	}
	
	/* (non-PHPdoc)
	 * @see WindModule::setConfig()
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		if ($this->_config) {
			$this->templateDir = $this->getConfig('template-dir', '', $this->templateDir);
			$this->templateExt = $this->getConfig('template-ext', '', $this->templateExt);
			$this->compileDir = $this->getConfig('compile-dir', '', $this->compileDir);
			$this->compileExt = $this->getConfig('compile-ext', '', $this->compileExt);
			$this->isCompile = $this->getConfig('is-compile', '', $this->isCompile);
			$this->layout = $this->getConfig('layout', '', $this->layout);
			$this->themePackPattern = $this->getConfig('themePackPattern', '', 
				$this->themePackPattern);
			$this->htmlspecialchars = $this->getConfig('htmlspecialchars', '', 
				$this->htmlspecialchars);
			$this->setThemePackage($this->getConfig('theme-package'));
		}
	}

	/**
	 * 返回模板绝对路径信息
	 * 
	 * 根据模板的逻辑名称,返回模板的绝对路径信息,支持命名空间方式定义模板信息.<code>
	 * $template='templateName'; //return $templateDir/templateName.$ext
	 * $template='subTemplateDir.templateName'; //return $templateDir/subTemplateDir/templateName.$ext
	 * $template='namespace:templateName'; //return namespace:templateName.$ext</code>
	 * <note><b>注意:</b>$template为空则返回当前的模板的路径信息.模板文件后缀名可以通过修改配置进行修改.</note>
	 * @param string $template 模板名称, 默认值为空 , 为空则返回当前模板的绝对地址
	 * @param string $ext 模板后缀, 默认值为空, 为空则返回使用默认的后缀
	 * @param boolean $createCompileDir true
	 * @return array(templatePath, compilePath, currentThemeKey)
	 */
	public function getViewTemplate($template = '', $ext = '') {
		$template || $template = $this->templateName;
		$ext || $ext = $this->templateExt;
		$compilePath = $templatePath = '';
		if ($this->templateDir && false === strpos($template, ':')) {
			$template = $this->templateDir . '.' . $template;
		}
		$_template = false !== ($pos = strpos($template, ':')) ? substr($template, $pos + 1) : $template;
		$currentThemeKey = null;
		foreach ($this->theme as $currentThemeKey => $theme) {
			$_templatePath = strtr($this->themePackPattern, 
				array('{pack}' => $theme[1], '{theme}' => $theme[0])) . '.' . $_template;
			$_templatePath = Wind::getRealPath($_templatePath, $ext, true);
			isset($_compileDir) || $_compileDir = $theme[0];
			if (is_file($_templatePath)) {
				$templatePath = $_templatePath;
				break;
			}
		}
		$templatePath === '' && $templatePath = Wind::getRealPath($template, $ext, true);
		$compilePath = $this->compileDir . '.' . (isset($_compileDir) ? $_compileDir . '.' : '') . $_template;
		$compilePath = Wind::getRealPath($compilePath, $this->compileExt, true);
		//WindFolder::mkRecur(dirname($compilePath));
		return array($templatePath, $compilePath, $currentThemeKey);
	}

	/**
	 * 获取当前风格定义
	 *
	 * @param string $type (all)
	 * @return array(theme,package)
	 */
	public function getTheme($key = 'all') {
		if ($key === 'all') return $this->theme;
		return $this->theme[$key];
	}

	/**
	 * 设置风格主题信息
	 * 
	 * @param string $theme
	 * @param string $package
	 */
	public function setTheme($theme, $package) {
		array_unshift($this->theme, array($theme, $package));
	}
}