<?php
Wind::import('WIND:cache.AbstractWindCache');
/**
 * memCache策略实现
 * 
 * memCache缓存允许将缓存保存到memCache内存缓存中.
 * 提供对方访问接口如下:
 * <ul>
 * <li>set($key, $value, $expire): 继承自{@link AbstractWindCache::set()}.</li>
 * <li>get($key): 继承自{@link AbstractWindCache::get()}.</li>
 * <li>delete($key): 继承自{@link AbstractWindCache::delete()}.</li>
 * <li>batchGet($keys): 继承自{@link AbstractWindCache::batchGet()}.</li>
 * <li>batchDelete($keys): 继承自{@link AbstractWindCache::batchDelete()}.</li>
 * <li>{@link setConfig($config)}: 重写了父类的{@link AbstractWindCache::setConfig()}.</li>
 * </ul>
 * 
 * 它接收如下配置:
 * <code>
 * array(
 * 'security-code' => '',	//继承自AbstractWindCache,安全码配置.
 * 'key-prefix' => '',	 //继承自AbstractWindCache,缓存key前缀.
 * 'expires' => '0',	//继承自AbstractWindCache,缓存过期时间配置.
 * 'compress' => '0',	//压缩等级,默认为0
 * 'servers' => array(
 * 'host' => array(
 * 'host'=>'localhost',	//要连接的memcached服务端监听的主机位置.
 * 'port'=>11211,	//要连接的memcached服务端监听的端口.
 * 'pconn'=>true,	//控制是否使用持久化连接,默认true.
 * 'weight' => 1,	//为此服务器创建的桶的数量,用来控制此服务器被选中的权重,单个服务器被选中的概率是相对于所有服务器weight总和而言的.
 * 'timeout' => 1,	//连接持续（超时）时间（单位秒）,默认值1秒.
 * 'retry' => 15,	//服务器连接失败时重试的间隔时间,默认值15秒.
 * 'status' => true,	//控制此服务器是否可以被标记为在线状态.
 * 'fcallback' => null,	//允许用户指定一个运行时发生错误后的回调函数
 * ),
 * ),
 * )
 * </code>
 * <i>使用方法：</i><br/>
 * 1、按照普通的调用类库的方式去调用:
 * <code>
 * Wind::import("WIND:cache.strategy.WindMemCache");
 * $cache = new WindMemCache();
 * $cache->setConfig(array('host' => 'localhost', 'port' => 11211));
 * $cache->set('name', 'test');
 * </code>
 * 2、采用组件配置的方式，通过组件机制调用
 * 在应用配置的components组件配置块中,配置memCache(<i>该名字将决定调用的时候使用的组件名字</i>):
 * <code>
 * 'memCache' => array(
 * 'path' => 'WIND:cache.strategy.WindMemCache',
		'scope' => 'singleton',
 * 'config' = array(
 * 'security-code' => '',
 * 'key-prefix' => '',
 * 'expires' => '0',
 * 'compress' => '0',
 * 'servers' => array(
 * 'host1' => array(
 * 'host'=>'localhost',
 * 'port'=>11211,
 * 'pconn'=>true,
 * 'weight' => 1,
 * 'timeout' => 15,
 * 'retry' => 15,
 * 'status' => true,
 * 'fcallback' => null,
 * ),
 * ),
 * ),
 * ),
 * </code>
 * 如果含有多个memCache主机,可以设置多组host在config中,如host1,host2,其key值也可以自定义.
 * 
 * <note><b>注意：</b>要使用该组件需要安装memcache扩展库.</note>
 * 
 * the last known user to change this file in the repository  <LastChangedBy: xiaoxiao >
 * @author xiaoxiao <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindMemCache.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package strategy
 */
class WindMemCache extends AbstractWindCache {
	
	/**
	 * memcache缓存操作句柄
	 * 
	 * @var WindMemcache 
	 */
	protected $memcache = null;
	
	/**
	 * 是否对缓存采取压缩存储
	 * 
	 * @var int 
	 */
	protected $compress = 0;

	/**
	 * 构造函数
	 * 
	 * 判断是否有支持memCache,如果没有安装扩展库将会抛出异常,<br/>
	 * 首先尝试使用memcached扩展，如果然后尝试创建memcache
	 * 
	 * @throws WindCacheException 如果没有安装memcache扩展则抛出异常
	 */
	public function __construct() {
		$this->memcache = new Memcache();
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::setValue()
	 */
	protected function setValue($key, $value, $expire = 0) {
		return $this->memcache->set($key, $value, $this->compress, (int) $expire);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::addValue()
	 */
	protected function addValue($key, $value, $expires = 0) {
		return $this->memcache->add($key, $value, $this->compress, (int) $expires);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::getValue()
	 */
	protected function getValue($key) {
		return $this->memcache->get($key, $this->compress);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::deleteValue()
	 */
	protected function deleteValue($key) {
		return $this->memcache->delete($key);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::clear()
	 */
	public function clear() {
		return $this->memcache->flush();
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::increment()
	 */
	public function increment($key, $step = 1) {
		return $this->memcache->increment($this->buildSecurityKey($key), $step);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::decrement()
	 */
	public function decrement($key, $step = 1) {
		return $this->memcache->decrement($this->buildSecurityKey($key), $step);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindCache::batchGet()
	 */
	public function batchGet(array $keys) {
		$_keys = array_map(array($this, 'buildSecurityKey'), $keys);
		$tags = array_fill(0, count($_keys), $this->compress);
		$_datas = $this->memcache->get($_keys, $tags);
		$_return = array();
		foreach ($keys as $_i => $key) {
			$_return[$key] = isset($_datas[$_keys[$_i]]) ? $this->formatData($key, $_datas[$_keys[$_i]]) : false;
		}
		return $_return;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindCache::buildData()
	 */
	protected function buildData($value, $expires = 0, IWindCacheDependency $dependency = null) {
		return $value;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindCache::formatData()
	 */
	protected function formatData($key, $value) {
		return $value;
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::setConfig()
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		$this->compress = $this->getConfig('compress', '', '0');
		$servers = $this->getConfig('servers', '', array());
		$defaultServer = array(
			'host' => '', 
			'port' => '', 
			'pconn' => true, 
			'weight' => 1, 
			'timeout' => 1, 
			'retry' => 15, 
			'status' => true, 
			'fcallback' => null);
		foreach ((array) $servers as $server) {
			if (!is_array($server)) throw new WindCacheException('[cache.strategy.WindMemCache.setConfig] The memcache config is incorrect');
			$args = array_merge($defaultServer, $server);
			if (empty($server['host'])) throw new WindCacheException('[cache.strategy.WindMemCache.setConfig] The memcache server ip address is not exist');
			if (empty($server['port'])) throw new WindCacheException('[cache.strategy.WindMemCache.setConfig] The memcache server port is not exist');
			$this->memcache->addServer($args['host'], $args['port'], $args['pconn'], $args['weight'], $args['timeout'], $args['retry'], $args['status'], $args['fcallback']);
		}
	}
}