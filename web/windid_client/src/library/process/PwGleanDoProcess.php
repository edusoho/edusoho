<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * Glean-Do(数据收集 - 操作)业务流程
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwGleanDoProcess.php 21318 2012-12-04 09:24:09Z jieyin $
 * @package forum
 */

abstract class PwGleanDoProcess extends PwBaseHookService {

	public function execute() {
		$this->init();
		foreach ($this->getData() as $key => $value) {
			$this->gleanData($value);
			$this->runDo('gleanData', $value);
		}
		if (($result = $this->run()) instanceof PwError) {
			return $result;
		}
		$this->runDo('run', $this->getIds());
		return true;
	}
	
	protected function init() {

	}

	abstract public function getData();

	abstract protected function gleanData($value);
	
	abstract public function getIds();

	abstract protected function run();

	protected function _getInterfaceName() {
		return 'iPwGleanDoHookProcess';
	}
}