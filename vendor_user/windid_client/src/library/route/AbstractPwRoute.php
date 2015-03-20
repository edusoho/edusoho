<?php
Wind::import('WIND:router.route.AbstractWindRoute');
/**
 * 基础路由
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AbstractPwRoute.php 25182 2013-03-06 07:54:07Z long.shi $
 * @package route
 */
abstract class AbstractPwRoute extends AbstractWindRoute {
	protected $default_m;
	
	public function __construct($default_m = '') {
		if ($default_m) {
			$this->default_m = $default_m;
		}
	}
	/**
	 * @return field_type
	 */
	public function getDefault_m() {
		return $this->default_m;
	}

	/**
	 * @param field_type $default_m
	 */
	public function setDefault_m($default_m) {
		$this->default_m = $default_m;
	}
	
}

?>