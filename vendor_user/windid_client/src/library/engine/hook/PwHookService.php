<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * pw扩展机制
 *
 * @author JianMin Chen <sky_hold@163.com> 2011-12-19
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwHookService.php 8692 2012-04-24 05:56:29Z jieyin $
 * @package wekit
 * @subpackage engine.hook
 */
class PwHookService extends PwBaseHookService {
	
	protected $_interface;

	/**
	 * 构造函数，默认启动埋在此钩子下的扩展服务
	 *
	 * @param string $hookKey 钩子点，默认为类名
	 * @param string $interface
	 * @param object $srv
	 * @return void
	 */
	public function __construct($hookKey, $interface, $srv = '') {
		parent::__construct($hookKey);
		$this->setSrv($srv);
		$this->_interface = $interface;
	}

	/**
	 * 指定扩展服务的接口名(或基类)
	 * 
	 * 该抽象方法返回一个类型定义{@see PwBaseHookService::appendDo}
	 * 注入到该服务的扩展必须为该类型.
	 * @return string
	 */
	protected function _getInterfaceName() {
		return $this->_interface;
	}
}
?>