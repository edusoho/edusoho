<?php
Wind::import('WIND:i18n.WindI18nException');
Wind::import('WIND:i18n.IWindLangResource');
/**
 * 语言资源基础实现
 * 语言资源基础实现,支持ini格式语言资源类型的解析,该语言资源组件基于wind组件模式进行开发.
 * 实现了语言包路径,默认语言文件,语言内容缓存等功能.
 * 
 * @example <code>
 *          LANG 为包名,如果不填写则默认没有分包处理,资源类将自动在language包下面寻找
 *          支持解析格式: LANG:login.fail.expty = 'xxx'
 *          </code>
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindLangResource.php 3850 2012-12-04 07:30:02Z yishuo $
 * @package i18n
 */
class WindLangResource extends WindModule implements IWindLangResource {
	/**
	 * 缓存key前缀
	 * 
	 * @var string
	 */
	protected $_cachePrefix = 'Wind.i18n.WindLangResource';
	/**
	 * 消息存储池
	 * 
	 * @var array
	 */
	protected $_messages = array();
	/**
	 * 默认资源文件
	 * 
	 * @var string
	 */
	protected $default;
	/**
	 * 资源文件后缀名定义
	 * 
	 * @var string
	 */
	protected $suffix;
	/**
	 * 语言包目录
	 * 
	 * @var string
	 */
	protected $path;
	/**
	 * 语言
	 * 
	 * @var string
	 */
	protected $language;
	
	/**
	 *
	 * @var WindLocale
	 */
	protected $locale = null;
	
	/*
	 * (non-PHPdoc) @see IWindLangResource::lang()
	 */
	public function getMessage($message, $params = array()) {
		$message = $this->translate($message);
		return empty($params) ? $message : WindUtility::strtr($message, $params);
	}

	/**
	 * 获取一条message信息
	 * 
	 * @param array $messages
	 * @return string
	 */
	protected function translate($message) {
		$package = $file = '';
		$_message = $message;
		if (strpos($_message, ':') != false) list($package, $_message) = explode(':', $_message, 2);
		if (strpos($_message, '.') != false) list($file, $key) = explode('.', $_message, 2);
		$path = $this->resolvedPath($package);
		if (is_file($path . '/' . $file . $this->suffix)) {
			$path = $path . '/' . $file . $this->suffix;
		} elseif (is_file($path . '/' . $this->default . $this->suffix)) {
			$path = $path . '/' . $this->default . $this->suffix;
			$key = $_message;
			$file = $this->default;
		} else
			return $message;
		
		if (!isset($this->_messages[$path])) {
			/* @var $cache AbstractWindCache */
			$cache = Wind::getComponent('windCache');
			$cacheKey = $this->_cachePrefix . $package . $file . filemtime($path);
			$messages = null;
			if ($cache) $messages = $cache->get($cacheKey);
			if (!$messages) {
				$messages = parse_ini_file($path);
				if ($cache) $cache->set($cacheKey, $messages);
			}
			$this->_messages[$path] = $messages;
		}
		return isset($this->_messages[$path][$key]) ? $this->_messages[$path][$key] : $message;
	}

	/**
	 * 解析资源文件路径信息
	 * 
	 * @param string $package
	 * @return string
	 */
	protected function resolvedPath($package) {
		$this->path || $this->path = Wind::getRootPath(Wind::getAppName());
		$this->language || $this->language = $this->getRequest()->getAcceptLanguage();
		$path = $this->path . '/' . $this->language . '/' . strtolower($package);
		$path = Wind::getRealDir(trim($path, '/'), true);
		return $path;
	}
	
	/*
	 * (non-PHPdoc) @see WindModule::setConfig()
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		$this->suffix = $this->getConfig('suffix', '', '');
		$this->default = $this->getConfig('default', '', 'message');
		$this->path = $this->getConfig('path', '', '');
		$this->language = $this->getConfig('language', '', 'zh_cn');
	}
}

?>