<?php
Wind::import('SRC:bootstrap.bootstrap');
Wind::import('ADMIN:service.srv.AdminUserService');

/**
 * @author Jianmin Chen <sky_hold@163.com>
 * @version $Id: adminBoot.php 26577 2013-04-11 08:05:09Z long.shi $
 * @package wekit
 */
abstract class adminBoot extends bootstrap {

	private $_loginUser = null;

	/**
	 * 后台菜单访问路径，
	 * 默认菜单地址‘APP:admin.conf.mainmenu.php’
	 *
	 * @var string
	 */
	public $menuPath = 'ADMIN:conf.mainmenu.php';
	
	/**
	 * 后台创始人配置地址，
	 * 默认菜单地址‘CONF:founder.php’
	 */
	public $founderPath = 'CONF:founder.php';

	/**
	 * 后台home页管理链接地址，
	 * 默认‘APP:admin.controller.HomeController’
	 *
	 * @var string
	 */
	public $homeLink = 'home/run';

	/**
	 * 搜索功能相关设置，
	 * 后台搜索功能是依赖于搜索文件的
	 * 搜索文件位置i18n/language/admin/searchFile
	 * 将搜索文件存放在语言包中，并指定相关搜索文件
	 *
	 * @var string
	 */
	public $searchFile = 'search';

	/**
	 * 后台log记录
	 *
	 * @var string
	 */
	public $logFile = 'DATA:log.admin_log.php';

	/**
	 * 数据表标识，
	 * 默认为空，为空时将不对数据表进行额外标识，所建立的数据表将为原始数据表
	 * 注意：当同个数据库下存在两套后台系统时，需要设置该项进行数据分离，否则会引起数据冲突。
	 *
	 * @var string
	 */
	public $dbTableMark = '';

	/**
	 * db组建名称，
	 * 默认为系统默认的db组建‘db’,如果需要启用其他的db组建，请设置改项
	 *
	 * @var string
	 */
	public $dbComponentName = 'db';

	/**
	 * 设置应用依赖服务配置
	 *
	 * @var array
	 */
	protected $dependenceServiceDefinitions = array(
		'adminUserService' => array('path' => '')
	);
	
	public function __construct($re) {
		if (!is_file(Wind::getRealPath('DATA:install.lock', true))) {
			Wind::getComponent('response')->sendRedirect("install.php");
		}
		parent::__construct($re);
	}

	public function getConfigService() {
		return Wekit::load('config.PwConfig');
	}

	/**
	 * 获取全局配置
	 *
	 * @return array
	 */
	public function getConfig() {
		return Wekit::cache()->get('config');
	}

	/**
	 * 获取当前时间戳
	 *
	 * @return int
	 */
	public function getTime() {
		$timestamp = time();
		if ($cvtime = Wekit::C('site', 'time.cv')) $timestamp += $cvtime * 60;
		return $timestamp;
	}
	
	public function getLoginUser() {
		if ($this->_loginUser === null) {
			$this->_loginUser = $this->_getLoginUser();
			$this->_loginUser->ip = Wind::getComponent('request')->getClientIp();
		}
		return $this->_loginUser;
	}
	
	/* (non-PHPdoc)
	 * @see bootstrap::beforeStart()
	 */
	public function beforeStart($front = null) {
		parent::beforeStart($front);
		foreach ($this->dependenceServiceDefinitions as $alias => $definition) {
			if (!$definition) continue;
			Wind::registeComponent($definition, $alias);
		}
	}

	/* (non-PHPdoc)
	 * @see bootstrap::beforeResponse()
	 */
	public function beforeResponse($front = null) {
		//后台搜索，加亮搜索关键字
		$searchword = Wind::getComponent('request')->getGet('searchword');
		if ($searchword) {
			$content = ob_get_contents();
			ob_end_clean();
			$content = preg_replace('/('.preg_quote($searchword, '/').')([^">;]*<)(?!\/script|\/textarea)/si','<span class="red"><u>\\1</u></span>\\2', $content);
			$compress = Wind::getApp()->getConfig('compress');
			if (!$compress || !ob_start('ob_gzhandler')) ob_start();
			echo $content;
		}
	}

	protected function _getLoginUser() {
		Wind::import('ADMIN:service.bo.AdminUserBo');
		$user = Wekit::load('ADMIN:service.srv.AdminUserService')->isLogin();
		return new AdminUserBo($user);
	}
}