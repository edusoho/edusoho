<?php
Wind::import('SRC:bootstrap.adminBoot');
Wind::import('SRV:user.bo.PwUserBo');

/**
 * pwadmin后台应用引导脚本
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package wind
 */
class pwadminBoot extends adminBoot {

	/**
	 * 后台菜单访问路径，
	 * 默认菜单地址‘APP:admin.conf.mainmenu.php’
	 *
	 * @var string
	 */
	public $menuPath = 'APPS:pwadmin.conf.mainmenu.php';

	/**
	 * 后台home页管理链接地址，
	 * 默认‘APP:admin.controller.HomeController’
	 *
	 * @var string
	 */
	public $homeLink = 'pwadmin/home/run';

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
	public $dbComponentName = '';

	/**
	 * 设置应用依赖服务配置
	 *
	 * @var array
	 */
	protected $dependenceServiceDefinitions = array(
		'adminUserService' => array('path' => 'APPS:pwadmin.service.srv.do.AdminUserDependenceService')
	);
	
	public function __construct($re)  {
		parent::__construct($re);

		//云应用监听sql执行
		WindFactory::_getInstance()->loadClassDefinitions(
			array(
				'sqlStatement' => array(
					'proxy' => 'WIND:filter.proxy.WindEnhancedClassProxy', 
					'listeners' => array('LIB:compile.acloud.PwAcloudDbListener'))));
	}

	/* (non-PHPdoc)
	 * @see phpwindBoot::init()
	 */
	public function beforeStart($front = null) {
		parent::beforeStart($front);
		if (!Wind::getComponent('router')->getRoute('pw')) {
			Wind::getComponent('router')->addRoute('pw', WindFactory::createInstance(Wind::import('LIB:route.PwRoute'), array('bbs')));
		}
		
	}
}
?>