<?php

/**
 * Dao的基类
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com> 2010-11-2
 * @license http://www.phpwind.com
 * @version $Id: WindidBaseDao.php 23671 2013-01-14 09:02:16Z jieyin $
 * @package Windid.library.base
 */
abstract class WindidBaseDao extends PwBaseDao {

	public function __construct() {
		$this->setDelayAttributes(array('connection' => array('ref' => 'windiddb')));
	}
}