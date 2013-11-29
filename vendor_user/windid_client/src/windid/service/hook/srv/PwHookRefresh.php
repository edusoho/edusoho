<?php
/**
 * hook列表更新
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwHookRefresh.php 24378 2013-01-29 09:13:10Z jieyin $
 * @package hook.srv
 */
class PwHookRefresh {
	
	public $conf = 'CONF:hooks.php';
	
	/**
	 * 由hooks.php导入数据库
	 *
	 * @return boolean
	 */
	public function refresh() {
		$conf = @include Wind::getRealPath($this->conf, true);
		if (!$conf || !is_array($conf)) return new PwError('fail');
		$hooks = $inject = array();
		foreach ($conf as $k => $v) {
			$hooks[] = array(
				'name' => $k,
				'app_name' => '系统',
				'created_time' => time(),
				'document' => implode("\r\n",
					array($v['description'], implode("\n", (array) $v['param']), $v['interface'])));
			foreach ($v['list'] as $k1 => $v1) {
				$inject[] = array(
					'hook_name' => $k,
					'app_id' => 'system',
					'app_name' => '系统',
					'alias' => $k1,
					'class' => $v1['class'],
					'method' => $v1['method'],
					'loadway' => $v1['loadway'],
					'expression' => $v1['expression'],
					'description' => $v1['description'],
					'created_time' => time());
			}
		}
		$this->_loadHooks()->delByAppId('');
		$this->_loadHookInject()->deleteByAppId('');
		$this->_loadHookInject()->deleteByAppId('system');
		$this->_loadHooks()->batchAdd($hooks);
		$this->_loadHookInject()->batchAdd($inject);
		return true;
	}
	

	/**
	 * @return PwHooks
	 */
	private function _loadHooks() {
		return Wekit::load('hook.PwHooks');
	}
	
	/**
	 * @return PwHookInject
	 */
	private function _loadHookInject() {
		return Wekit::load('hook.PwHookInject');
	}
}

?>