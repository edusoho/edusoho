<?php
/**
 * 搜索
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwHookSo.php 13528 2012-07-09 08:43:20Z long.shi $
 * @package hook.dm
 */
class PwHookSo {
	protected $_data = array();
	
	/**
	 * 设置hook名
	 *
	 * @param string $v
	 * @return PwHookSo
	 */
	public function setName($v) {
		$v && $this->_data['name'] = $v;
		return $this;
	}
	
	/**
	 * 应用名称
	 *
	 * @param string $v
	 * @return PwHookSo
	 */
	public function setAppName($v) {
		$v && $this->_data['app_name'] = $v;
		return $this;
	}
	
	public function getData() {
		return $this->_data;
	}
}

?>