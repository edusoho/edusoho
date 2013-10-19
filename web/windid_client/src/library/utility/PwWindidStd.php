<?php
/**
 * windid虚拟类，用于windid的通知DS层兼容
 * the last known user to change this file in the repository  <$LastChangedBy: jieyin $>
 * @author $Author: jieyin $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwWindidStd.php 24765 2013-02-20 09:47:22Z jieyin $ 
 * @package 
 *
 */
class PwWindidStd {
	
	protected $api = '';
	private $method = '';
	private $value = '';
	private static $_instance = null;
	
	public function __construct($api){
		$this->api = $api;
	}
	
	public static function getInstance($api) {
		isset(self::$_instance) || self::$_instance = new self($api);
		return self::$_instance;
	}
	
	public function setMethod($method, $value) {
		$this->method = $method;
		$this->value = $value; 
	}
	
	public function __call($method, $args) {
		if ($this->method == $method) return $this->value;
		$cls = WindidApi::api($this->api);
		return call_user_func_array(array($cls, $method), $args);
	}
}
?>