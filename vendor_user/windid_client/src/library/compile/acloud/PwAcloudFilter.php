<?php
Wind::import('LIB:compile.acloud.PwAcloudDataMapper');

/**
 * Acloud服务相关
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwAcloudFilter.php 22371 2012-12-21 13:10:32Z yishuo $
 * @package wekit.compile.acloud
 */
class PwAcloudFilter extends AbstractWindBootstrap {
	/*
	 * (non-PHPdoc) @see WindHandlerInterceptor::preHandle()
	*/
	public function onCreate() {}
	
	/*
	 * (non-PHPdoc) @see AbstractWindBootstrap::onStart()
	*/
	public function onStart() {}
	
	/*
	 * (non-PHPdoc) @see AbstractWindBootstrap::onResponse()
	*/
	public function onResponse() {
		if (Wind::getApp()->getRequest()->getIsAjaxRequest()) return;
		$_var = Wind::getApp()->getResponse()->getData('_aCloud_');
		if (!is_array($_var) || in_array($_var['m'], array('design', 'cron', 'windid'))) return;
		require_once Wind::getRealPath('ACLOUD:aCloud');
		$dataMapper = new PwAcloudDataMapper();
		if (null !== $collect = $this->getCollect($_var['c'])) {
			if (!$collect->isCollect($_var['a'])) return;
			$vars = Wind::getApp()->getResponse()->getData($_var['current']);
			$collect->collect($dataMapper, $vars);
		}
		$dataMapper->setSrc($_var['c']);
		$dataMapper->setUid(Wekit::getLoginUser()->uid);
		$dataMapper->setUsername(Wekit::getLoginUser()->username);
		$dataMapper->setCharset(Wind::getApp()->getResponse()->getCharset());
		
		Wind::getApp()->getResponse()->setBody(ACloudAppGuiding::getApp($dataMapper), 'acloud');
	}

	/**
	 * 获得收集页面
	 *
	 * @return AbstractCollect
	 */
	private function getCollect($c) {
		$types = array();
		$types['read'] = 'LIB:compile.acloud.collect.PwAcloudReadCollect';
		$types['thread'] = 'LIB:compile.acloud.collect.PwAcloudThreadCollect';
		$types['index'] = 'LIB:compile.acloud.collect.PwAcloudIndexCollect';
		if (!$types[$c]) return null;
		$class = Wind::import($types[$c]);
		return new $class();
	}
}