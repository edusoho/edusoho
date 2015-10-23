<?php
Wind::import('WIND:filter.WindEnhancedListener');
require_once Wind::getRealPath('ACLOUD:aCloud');
/**
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright 2003-2103 phpwind.com
 * @license http://www.phpwind.com/license.php
 * @version $Id$
 * @package wind
 */
class PwAcloudDbListener extends WindEnhancedListener {

	protected function postexecute($params = array(), $rowCount = false) {
		return $this->collectSql($this->targetObject->getQueryString(), 
			$this->targetObject->getParams());
	}

	protected function postupdate($params = array(), $rowCount = false) {
		return $this->collectSql($this->targetObject->getQueryString(), 
			$this->targetObject->getParams());
	}

	private function collectSql($queryString, $params) {
		return ACloudAppGuiding::collectSql($queryString, $params);
	}
}