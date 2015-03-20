<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: jieyin $>
 * @author $Author: jieyin $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidMessageSo.php 23620 2013-01-14 02:44:14Z jieyin $ 
 * @package 
 */
class WindidMessageSo {
	
	protected $_data = array();
	
	public function getData() {
		return $this->_data;
	}
	
	public function setFromUid($fromuid) {
		$this->_data['fromuid'] = (int)$fromuid;
	}
	
	public function setToUid($touid) {
		$this->_data['touid'] = (int)$touid;
	}

	public function setKeyword($keyword) {
		$this->_data['keyword'] = $keyword;
	}
	
	public function setStarttime($starttime) {
		$this->_data['starttime'] = $starttime;
	}
	
	public function setEndTime($time) {
		$this->_data['endtime'] = $time;
	}

}
?>