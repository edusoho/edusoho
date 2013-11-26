<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: jieyin $>
 * @author $Author: jieyin $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidStoreService.php 24398 2013-01-30 02:45:05Z jieyin $ 
 * @package 
 */

class WindidStoreService {
	
	public function getStore() {
		$ds = Wekit::load('WSRV:config.WindidConfig');
		$stores = $ds->getValues('storage');
		$config = $ds->getValues('attachment');
		$config = $config['storage.type'];
		if (!$config || !isset($stores[$config])) {
			$cls = 'WINDID:library.storage.WindidStorageLocal';
		} else {
			$store = unserialize($stores[$config]);
			$cls = $store['components']['path'];
		}
		$srv = Wind::import($cls);
		return new $srv();
		//$this->store = Wind::getComponent($this->bhv->isLocal ? 'windidLocalStorage' : 'windidStorage');
	}
	
	public function setStore($key, $storage) {
		Wind::import('WSRV:config.srv.WindidConfigSet');
		$config = new WindidConfigSet('storage');
		$config->set($key, serialize($storage))->flush();
		return true;
	}
	
}
?>