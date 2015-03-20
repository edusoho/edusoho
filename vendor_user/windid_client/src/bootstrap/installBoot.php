<?php
Wind::import('SRV:user.bo.PwUserBo');
Wind::import('SRC:bootstrap.bootstrap');

class installBoot extends bootstrap {
	
	public function getConfigService() {
		return Wekit::load('config.PwConfig');
	}

	public function getConfig() {
		return array('components'=>array(), 'site' => array('debug' => 0));
	}

	public function getTime() {
		return time();
	}

	/* (non-PHPdoc)
	 * @see bootstrap::getLoginUser()
	 */
	public function getLoginUser() {
		return null;
	}
}