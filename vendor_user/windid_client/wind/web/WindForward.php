<?php
/**
 * 操作转发类,该类携带了操作转发信息以及后续处理信息给主应用控制器
 * 
 * 所有的操作处理类中,都默认包含了一个WindForward对象,当操作处理结束后返回该对象给主应用控制器.
 * 该类中包含了,变量信息,视图处理信息,跳转信息布局信息等.
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindForward.php 3533 2012-05-08 08:24:20Z yishuo $
 * @package web
 */
class WindForward extends WindModule {
	/**
	 * 定义视图处理器
	 * 
	 * @var WindView
	 */
	protected $windView = null;
	/**
	 * @see WindModule::_delayAttributes
	 */
	protected $_delayAttributes = array('windView' => array('ref' => 'windView'));
	/**
	 * 存储变量
	 * 
	 * @var array
	 */
	private $vars = array();
	/**
	 * 是否为Action请求
	 * 
	 * @var boolean
	 */
	private $isReAction = false;
	/**
	 * 是否是重定向请求
	 * 
	 * @var boolean
	 */
	private $isRedirect = false;
	/**
	 * 跳转链接
	 * 
	 * @var string
	 */
	private $url;
	private $action;
	private $args = array();

	/**
	 * 将请求重定向到另外一个Action操作
	 * 
	 * 参数支持格式:module/controller/action/?a=&b=&c=
	 * @param string $action $action 操作
	 * @param array $args 参数 默认为空数组
	 * @param boolean $isRedirect 是否重定向  默认为false
	 * @param boolean $immediately 是否理解重定向 默认为true
	 * @return void
	 * @throws WindForwardException
	 */
	public function forwardAction($action, $args = array(), $isRedirect = false, $immediately = true) {
		$this->setIsReAction(true);
		$this->setAction($action);
		$this->setArgs($args);
		$this->setIsRedirect($isRedirect);
		if ($immediately) throw new WindForwardException($this);
	}

	/**
	 * url重定向
	 * 
	 * 采用<b>head</b>方式，将当前的请求重定向到新的url地址
	 * @param string $url 重定向的url地址
	 * @return void
	 * @throws WindForwardException
	 */
	public function forwardRedirect($url) {
		$this->setIsRedirect(true);
		$this->setUrl($url);
		throw new WindForwardException($this);
	}

	/**
	 * 设置当前forward对象中存储的变量
	 * 
	 * 设置当前forward对象中存储的变量，设置到forward中的所有变量都可以在模板页面中被直接访问到
	 * @param string|array|object $vars
	 * @param string $key 默认为空字符串
	 * @return void
	 */
	public function setVars($vars, $key = '', $merge = false) {
		if (!$key) {
			if (is_object($vars)) $vars = get_object_vars($vars);
			if (is_array($vars)) $this->vars = array_merge($this->vars, $vars);
		} elseif ($merge && !empty($this->vars[$key])) {
			$this->vars[$key] = WindUtility::mergeArray((array) $this->vars[$key], (array) $vars);
		} else
			$this->vars[$key] = $vars;
	}

	/**
	 * 返回当前forward对象中存储的变量信息
	 * 
	 * 返回当前forward对象中存储的变量信息，支持多个参数，当参数为空时返回全部的变量信息
	 * @return mixed
	 */
	public function getVars() {
		$_tmp = $this->vars;
		foreach (func_get_args() as $arg) {
			if (is_array($_tmp) && isset($_tmp[$arg]))
				$_tmp = $_tmp[$arg];
			else
				return '';
		}
		return $_tmp;
	}

	/**
	 * @return WindView
	 */
	public function getWindView() {
		if ($this->windView === null) {
			$this->_getWindView();
			$module = Wind::getApp()->getModules();
			if (isset($module['template-path'])) {
				$this->windView->templateDir = $module['template-path'];
			}
			if (isset($module['compile-path'])) {
				$this->windView->compileDir = $module['compile-path'];
			}
			if (isset($module['theme-path'])) {
				$this->windView->setThemePackage($module['theme-path']);
			}
		}
		return $this->windView;
	}

	/**
	 * @param WindView $windView
	 */
	public function setWindView($windView) {
		$this->windView = $windView;
	}

	/**
	 * @return boolean
	 */
	public function getIsRedirect() {
		return $this->isRedirect;
	}

	/**
	 * @param boolean $isRedirect
	 */
	public function setIsRedirect($isRedirect) {
		$this->isRedirect = $isRedirect;
	}

	/**
	 * @return boolean
	 */
	public function getIsReAction() {
		return $this->isReAction;
	}

	/**
	 * @param boolean $isReAction
	 */
	public function setIsReAction($isReAction) {
		$this->isReAction = $isReAction;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @param string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * @param string $action
	 */
	public function setAction($action) {
		$this->action = $action;
	}

	/**
	 * @return array
	 */
	public function getArgs() {
		return $this->args;
	}

	/**
	 * @param array
	 */
	public function setArgs($args) {
		$this->args = $args;
	}
}