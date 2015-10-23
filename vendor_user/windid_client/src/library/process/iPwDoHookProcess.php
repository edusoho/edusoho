<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('LIB:process.PwGleanDoProcess');

/**
 * Do(操作)业务流程(扩展)
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: iPwDoHookProcess.php 5382 2012-03-03 07:29:14Z jieyin $
 * @package forum
 */

abstract class iPwDoHookProcess {

	public $srv;
	
	public function __construct($srv) {
		$this->srv = $srv;
	}

	public function run($ids) {

	}
}