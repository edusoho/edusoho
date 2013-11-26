<?php
Wind::import('WIND:cache.AbstractWindCache');
/**
 * 基于phpredis扩展的redisCache实现类
 * <note><b>注意：</b>使用该类型的缓存，需要安装phpredis扩展</note>
 * 配置文件格式：
 * <code>
 * array(
 * 'security-code' => '',	//继承自AbstractWindCache,安全码配置.
 * 'key-prefix' => '', //继承自AbstractWindCache,缓存key前缀.
 * 'expires' => '0',	//继承自AbstractWindCache,缓存过期时间配置.
 * 'auth' => '',	//链接认证密码，redis->auth
 * 'servers' => array(
 * array(
 * 'host'=>'127.0.0.1',	//要连接的redis服务端监听的主机位置.
 * 'port'=>6379,	//要连接的redis服务端监听的端口.
 * 'timeout' => 0,	//连接持续（超时）时间（单位秒）,默认值0.不限制
 * 'pconn'=>true,	//控制是否使用持久化连接,默认true.
 * ),
 * ),
 * )
 * </code>
 * 1、按照普通的调用类库的方式去调用:
 * <code>
 * Wind::import("WIND:cache.strategy.WindRedisCache");
 * $cache = new WindRedisCache();
 * $cache->setConfig(array('host' => '127.0.0.1', 'port' => 6379));
 * $cache->set('name', 'test');
 * echo $cache->get('name');
 * </code>
 * 2、采用组件配置的方式，通过组件机制调用
 * 在应用配置的components组件配置块中,配置redisCache(<i>该名字将决定调用的时候使用的组件名字</i>):
 * <code>
 * //配置组件
 * 'redisCache' => array(
 * 'path' => 'WIND:cache.strategy.WindRedisCache',
 * 'scope' => 'singleton',
 * 'config' = array(
 * 'security-code' => '',
 * 'key-prefix' => '',
 * 'expires' => '0',
 * 'auth' => '',
 * 'servers' => array(
 * 'host1' => array(
 * 'host' => '127.0.0.1',
 * 'port' => 6379,
 * 'pconn' => true,
 * 'timeout' => 0,
 * ),
 * ),
 * ),
 * ),
 * //使用：
 * $redis = Wind::getComponent('redisCache');
 * $redis->set('name', 'windFramework');
 * echo $redis->get('name');
 * </code>
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindRedisCache.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package wind.cache.strategy
 */
class WindRedisCache extends AbstractWindCache {
	/**
	 * redis缓存操作句柄
	 * 
	 * @var Redis
	 */
	protected $redis = null;
	
	/**
	 * redis链接使用的认证密码
	 * 
	 * @var string
	 */
	protected $auth = '';

	/**
	 * 构造函数
	 * 
	 * @throws WindCacheException
	 */
	public function __construct() {
		$this->redis = new Redis();
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::setValue()
	 */
	protected function setValue($key, $value, $expires = 0) {
		$r = $this->redis->set($key, $value);
		if ($r && $expires) $this->redis->setTimeout($key, $expires);
		return $r;
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::addValue()
	 */
	protected function addValue($key, $value, $expires = 0) {
		$r = $this->redis->setnx($key, $value);
		if ($r && $expires) {
			$this->redis->setTimeout($key, $expires);
		}
		return $r;
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::getValue()
	 */
	protected function getValue($key) {
		return $this->redis->get($key);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::deleteValue()
	 */
	protected function deleteValue($key) {
		return $this->redis->delete($key);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::clear()
	 */
	public function clear() {
		$this->redis->flushAll();
	}
	
	/*
	 * //TODO 调用其他方法开放调用，重构 @see WindModule::__call()
	 */
	public function __call($methodName, $args) {
		if (!method_exists($this->redis, $methodName)) throw new WindCacheException('[cache.strategy.WindRedisCache] The method "' . $methodName . '" is no in redis');
		return call_user_func_array(array($this->redis, $methodName), $args);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::setConfig()
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		$auth = $this->getConfig('auth', '', '');
		if ($auth && (true !== $this->redis->auth($auth))) {
			throw new WindCacheException('[cache.strategy.WindRedisCache.setConfig] Authenticate the redis connection error');
		}
		$servers = $this->getConfig('servers', '', array());
		$defaultServer = array('host' => '', 'port' => 6379, 'timeout' => 0, 'pconn' => false, 'persistent_id' => '');
		foreach ((array) $servers as $server) {
			if (!is_array($server)) throw new WindCacheException('[cache.strategy.WindRedisCache.setConfig] The redis config is incorrect');
			$args = array_merge($defaultServer, $server);
			if (!isset($server['host'])) throw new WindCacheException('[cache.strategy.WindRedisCache.setConfig] The redis server ip address is not exist');
			$method = $args['pconn'] === true ? 'pconnect' : 'connect';
			$m_args = array($args['host'], $args['port'], $args['timeout']);
			// 如果是长链接，则会存在一个长链接的ID号
			($args['pconn'] === true && $args['persistent_id']) && $m_args[] = $args['persistent_id'];
			call_user_func_array(array($this->redis, $method), $m_args);
		}
	}
}