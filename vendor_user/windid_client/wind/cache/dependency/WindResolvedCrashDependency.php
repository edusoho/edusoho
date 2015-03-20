<?php
Wind::import('WIND:cache.IWindCacheDependency');
/**
 * cache依赖实现,通过该实现解决大并发时cache(memcached)的值突然失效
 * 
 * 在value内部设置1个超时值(timeout1), timeout1比实际的cache(memcached)timeout(timeout2)小.
 * 当从cache读取到timeout1发现它已经过期时候,马上延长timeout1并重新设置到cache.
 * 然后再从数据库加载数据并设置到cache中.
 * <note><b>注意:</b>该方法用回调的方式来获得当前缓存的数据值.</note>
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindResolvedCrashDependency.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package cache
 * @subpackage dependency
 */
class WindResolvedCrashDependency implements IWindCacheDependency {
	
	private $timeOut = '';
	
	private $callBack = array();
	
	private $args = array();

	/**
	 * @param array $callBack 回调方法,通过该方法返回当前数据
	 * @param array $args 回调方法参数
	 * @param int $timeOut 超时时间
	 */
	public function __construct($callBack, $args = array(), $timeOut = 0) {
		$this->timeOut = (int) $timeOut + time();
		$this->callBack = serialize($callBack);
		$this->args = serialize($args);
	}

	/* (non-PHPdoc)
	 * @see IWindCacheDependency::injectDependent()
	 */
	public function injectDependent($expires) {
		if ($this->timeOut > 0) return;
		$this->timeOut = $expires > 0 ? 0.8 * $expires + time() : 0;
	}

	/* (non-PHPdoc)
	 * @see IWindCacheDependency::hasChanged()
	 */
	public function hasChanged($cache, $key, $expires) {
		if (0 == $this->timeOut) return false;
		if ($this->timeOut <= time()) {
			$lock = $key . '_lock_';
			if ($cache->add($lock, 3 * 60 * 1000) == true) {
				$callBack = unserialize($this->callBack);
				$data = call_user_func_array($callBack, unserialize($this->args));
				$cache->set($key, $data, $expires, $this);
				$cache->delete($lock);
			}
		}
		return false;
	}
}