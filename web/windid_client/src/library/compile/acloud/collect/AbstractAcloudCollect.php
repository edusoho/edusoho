<?php

/**
 * 模板变量收集
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AbstractAcloudCollect.php 6816 2012-03-26 12:03:38Z xiaoxia.xuxx $
 * @package wekit.compile.acloud.collect
 */
abstract class AbstractAcloudCollect {
	/**
	 * 需要收集的action
	 *
	 * @var array
	 */
	protected $collectActions = array('run');
	/**
	 * 收集模板
	 * 
	 * @param PwAcloudDataMapper $dataMapper
	 * @param array 模板中的变量
	 */
	abstract public function collect(PwAcloudDataMapper $dataMapper, $template);
	
	/**
	 * 检测该模板是否需要收集变量
	 *
	 * @param string $a
	 * @return boolean
	 */
	public function isCollect($a) {
		return in_array(strtolower($a), $this->collectActions);
	}
}