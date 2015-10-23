<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('LIB:process.PwGleanDoProcess');

/**
 * Glean-Do(数据收集 - 操作)业务流程扩展
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: iPwGleanDoHookProcess.php 5382 2012-03-03 07:29:14Z jieyin $
 * @package forum
 */

abstract class iPwGleanDoHookProcess {

	public $srv;
	
	public function __construct($srv) {
		$this->srv = $srv;
	}

	public function gleanData($value) {

	}

	public function run($ids) {

	}
}