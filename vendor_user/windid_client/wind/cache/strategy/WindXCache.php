<?php
Wind::import('WIND:cache.AbstractWindCache');
/**
 * WindXCache封装了xCache实现缓存策略
 * 提供对方访问接口如下:
 * <ul>
 * <li>set($key, $value, $expire): 继承自{@link AbstractWindCache::set()}.</li>
 * <li>get($key): 继承自{@link AbstractWindCache::get()}.</li>
 * <li>delete($key): 继承自{@link AbstractWindCache::delete()}.</li>
 * <li>batchGet($keys): 继承自{@link AbstractWindCache::batchGet()}.</li>
 * <li>batchDelete($keys): 继承自{@link AbstractWindCache::batchDelete()}.</li>
 * <li>{@link setConfig($config)}: 重写了父类的{@link
 * AbstractWindCache::setConfig()}.</li>
 * </ul>
 * 它接收如下配置:
 * <code>
 * array(
 * 'user' => '',	//拥有清空xcache数据的权限用户
 * 'pwd' => '',	//拥有清空xcache数据的权限用户的密码
 * 'security-code' => '',	//继承自AbstractWindCache,安全码配置
 * 'key-prefix' => '', //继承自AbstractWindCache,缓存key前缀
 * 'expires' => '0',	//继承自AbstractWindCache,缓存过期时间配置
 * )
 * </code>
 * <i>xcache缓存的使用:</i><br/>
 * 1、像使用普通类库一样使用该组件:
 * <code>
 * Wind::import('WIND:cache.strategy.WindXCache');
 * $cache = new WindxCache();
 * $cache->set('name', 'windDbTest');
 * </code>
 * 2、采用组件配置的方式，通过组件机制调用
 * 在应用配置的components组件配置块中,配置xCache(<i>该名字将决定调用的时候使用的组件名字</i>):
 * <code>
 * 'xCache' => array(
 * 'path' => 'WIND:cache.strategy.WindXCache',
 * 'scope' => 'singleton',
 * 'config' => array(
 * 'user' => '',
 * 'pwd' => '',
 * 'security-code' => '',
 * 'key-prefix' => '',
 * 'expires' => '0',
 * ),
 * ),
 * </code>
 * 在应用中可以通过如下方式获得xCache对象:
 * <code>
 * $cache = Wind::getComponent('xCache');	//xCache的名字来自于组件配置中的名字
 * $cache->set('name', 'test');
 * </code>
 * <note><b>注意: </b>该组件需要安装扩展xcache.</note>
 * the last known user to change this file in the repository <LastChangedBy:
 * xiaoxiao >
 * 
 * @author xiaoxiao <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindXCache.php 3791 2012-10-30 04:01:29Z liusanbian $
 * @package strategy
 */
class WindXCache extends AbstractWindCache {
	/**
	 * 拥有删除数据的权限用户
	 * xcache清空缓存的时候需要获得有权限的用户
	 * 
	 * @var string
	 */
	private $authUser = '';
	/**
	 * 拥有删除数据的权限用户的密码
	 * xcache清空缓存的时候需要获得有权限的用户
	 * 
	 * @var string
	 */
	private $authPwd = '';
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::setValue()
	 */
	protected function setValue($key, $value, $expire = 0) {
		return xcache_set($key, $value, $expire);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::addValue()
	 */
	protected function addValue($key, $value, $expire = 0) {
		return xcache_set($key, $value, $expire);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::getValue()
	 */
	protected function getValue($key) {
		return xcache_get($key);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::deleteValue()
	 */
	protected function deleteValue($key) {
		return xcache_unset($key);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::clear()
	 */
	public function clear() {
		// xcache_clear_cache需要验证权限
		$tmp['user'] = isset($_SERVER['PHP_AUTH_USER']) ? null : $_SERVER['PHP_AUTH_USER'];
		$tmp['pwd'] = isset($_SERVER['PHP_AUTH_PW']) ? null : $_SERVER['PHP_AUTH_PW'];
		$_SERVER['PHP_AUTH_USER'] = $this->authUser;
		$_SERVER['PHP_AUTH_PW'] = $this->authPwd;
		// 如果配置中xcache.var_count > 0 则不能用xcache_clear_cache(XC_TYPE_VAR, 0)的方式删除
		$max = xcache_count(XC_TYPE_VAR);
		for ($i = 0; $i < $max; $i++) {
			xcache_clear_cache(XC_TYPE_VAR, $i);
		}
		// 恢复之前的权限
		$_SERVER['PHP_AUTH_USER'] = $tmp['user'];
		$_SERVER['PHP_AUTH_PW'] = $tmp['pwd'];
		return true;
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::setConfig()
	 */
	public function setConfig($config = array()) {
		if (!$config) return false;
		parent::setConfig($config);
		$this->authUser = $this->getConfig('user');
		$this->authPwd = $this->getConfig('pwd');
	}
}