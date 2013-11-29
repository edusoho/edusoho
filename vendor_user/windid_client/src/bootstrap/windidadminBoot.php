<?php
Wind::import('SRC:bootstrap.adminBoot');
Wind::import('WSRV:base.WindidBaseDao');
Wind::import('WSRV:base.WindidUtility');
Wind::import('WSRV:base.WindidError');

/**
 * windidadmin后台应用引导脚本
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: windidadminBoot.php 25243 2013-03-07 15:29:09Z long.shi $
 * @package wind
 */
class windidadminBoot extends adminBoot {

	private $_loginUser = null;

	/**
	 * 后台菜单访问路径，
	 * 默认菜单地址‘APP:admin.conf.mainmenu.php’
	 *
	 * @var string
	 */
	public $menuPath = 'APPS:windidadmin.conf.mainmenu.php';

	/**
	 * 后台home页管理链接地址，
	 * 默认‘APP:admin.controller.HomeController’
	 *
	 * @var string
	 */
	public $homeLink = 'windidadmin/home/run';

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
	public $logFile = 'DATA:log.windid_admin_log.php';

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
	public $dbComponentName = 'windiddb';

	/**
	 * 设置应用依赖服务配置
	 *
	 * @var array
	 */
	protected $dependenceServiceDefinitions = array(
		'adminUserService' => array('path' => 'APPS:windidadmin.service.srv.do.AdminUserDependenceService')
	);
	
	public function getCache() {
		return null;
	}
	
	public function getConfigService() {
		return Wekit::load('WSRV:config.WindidConfig');
	}

	/**
	 * 获取全局配置
	 *
	 * @return array
	 */
	public function getConfig() {
		return $this->getConfigCacheValue();
	}
	
	public function getTime() {
		$timestamp = time();
		if ($cvtime = Wekit::C('site', 'time.cv')) $timestamp += $cvtime * 60;
		return $timestamp;
	}

	/* (non-PHPdoc)
	 * @see phpwindBoot::init()
	 */
	public function beforeStart($front = null) {
		parent::beforeStart($front);
		if (!Wind::getComponent('router')->getRoute('pw')) {
			Wind::getComponent('router')->addRoute('pw', WindFactory::createInstance(Wind::import('LIB:route.PwRoute'), array('bbs')));
		}
		Wind::getComponent('router')->addRoute('admin', WindFactory::createInstance(Wind::import('LIB:route.PwAdminRoute'), array('default')), true);
		Wekit::setapp('windid', Wekit::app());
	}

	protected function getConfigCacheValue() {
		$vkeys = array('site', 'components', 'verify');
		$array = Wekit::load('WSRV:config.WindidConfig')->fetchConfig($vkeys);
		$config = array();
		foreach ($vkeys as $key => $value) {
			$config[$value] = array();
		}
		foreach ($array as $key => $value) {
			$config[$value['namespace']][$value['name']] = $value['vtype'] != 'string' ? unserialize($value['value']) : $value['value'];
		}
		return $config;
	}
}
?>