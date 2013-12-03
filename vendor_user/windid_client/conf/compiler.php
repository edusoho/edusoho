<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * 视图编译器配置文件
 * 
 * @author Qiong Wu <papa0924@gmail.com> 2010-11-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2010 phpwind.com
 * @license
 */
return array(
	'support-tags' => array(
		'design' => array(
			'tag' => 'design',
			'compiler' => 'LIB:engine.extension.viewer.PwTemplateCompilerDesign',
		),
		'portal' => array(
			'tag' => 'pw',
			'compiler' => 'LIB:engine.extension.viewer.PwTemplateCompilerPortal',
			'pattern' => '/\<pw-start\/>(.+)<pw-end\/>/isU'
		),
		'page' => array(
			'tag' => 'page',
			'compiler' => 'LIB:engine.extension.viewer.PwTemplateCompilerPage'
		),
		'component'	=> array(
			'tag' => 'component',
			'compiler' => 'LIB:engine.extension.viewer.PwTemplateCompilerComponent'
		),
		'hook' => array(
			'tag' => 'hook',
			'compiler' => 'LIB:engine.extension.viewer.PwTemplateCompilerHook'
		),
		'config' => array(
			'tag' => 'config',
			'compiler' => 'LIB:engine.extension.viewer.PwTemplateCompilerConfig',
			'pattern' => '/{@C:[^\}]*}/i'
		),
		'themeUrl' => array(
			'tag' => 'themeUrl',
			'compiler' => 'LIB:engine.extension.viewer.PwTemplateCompilerThemeUrl',	
			'pattern' => '/{@theme:[^\}]*}/i'
		),
		'url' => array(
			'tag' => 'url',
			'compiler' => 'LIB:engine.extension.viewer.PwTemplateCompilerUrlCreater',
			'pattern' => '/{@url:[^\}]*}/i'
		),
		'segment' => array(
			'tag' => 'segment',
			'compiler' => 'LIB:engine.extension.viewer.PwTemplateCompilerSegment'
		),
		'advertisement' => array(
			'tag' => 'advertisement',
			'compiler' => 'LIB:engine.extension.viewer.PwTemplateCompilerAdvertisement'
		),
		'csrftoken' => array(
			'tag' => 'csrftoken',
			'compiler' => 'LIB:engine.extension.viewer.PwTemplateCompilerCsrftoken',
			'pattern' => '/<\/form>/i'
		)
	)
);