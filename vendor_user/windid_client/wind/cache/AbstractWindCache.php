<?php
Wind::import('WIND:cache.exception.WindCacheException');
/**
 * 缓存策略实现的基类
 * 
 * 该基类继承了框架的基类WindModule,用以提供实现组件的一些特性.同时该类作为缓存策略的基类定义了通用的对方访问接口,及子类需要实现的抽象接口.
 * 定义了缓存可以访问的接口如下:
 * <ul>
 * <li>{@link set}: 保存缓存数据,需要子类实现{@link setValue()}方法实现对应具体的方法.</li>
 * <li>{@link get}: 获得缓存数据,需要子类实现{@link getValue()}方法来实现对应具体的方法.</li>
 * <li>{@link batchGet}: 批量获取缓存数据.</li>
 * <li>{@link delete}: 删除缓存数据,需要子类实现{@link deleteValue()}方法来实现对应具体的方法.</li>
 * <li>{@link batchDelete}: 批量删除缓存数据.</li>
 * </ul>
 * 该基类支持三个配置项{@link setConfig()}，所有继承该类的子类都拥有对该三个配置项的配置。
 * 
 * the last known user to change this file in the repository  <$LastChangedBy: yishuo $>
 * @author Su Qian <aoxue.1988.su.qian@163.com> 
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AbstractWindCache.php 3904 2013-01-08 07:01:26Z yishuo $ 
 * @package cache
 */
abstract class AbstractWindCache extends WindModule {
	/**
	 * key的安全码
	 * 
	 * @var string
	 */
	private $securityCode = '';
	/**
	 * 缓存前缀
	 * 
	 * @var sting 
	 */
	private $keyPrefix = '';
	/**
	 * 缓存过期时间
	 * 
	 * @var int
	 */
	private $expire = 0;
	/**
	 * 缓存依赖的类名称
	 * 
	 * @var string
	 */
	const DEPENDENCYCLASS = 'dependencyclass';
	/**
	 * 标志存储时间
	 * 
	 * @var string
	 */
	const STORETIME = 'store';
	/**
	 * 标志存储数据
	 * 
	 * @var string 
	 */
	const DATA = 'data';
	/**
	 * 配置文件中标志缓存依赖名称的定义
	 * 
	 * @var string 
	 */
	const DEPENDENCY = 'dependency';
	/**
	 * 配置文件中标志过期时间名称定义(也包含缓存元数据中过期时间的定义)
	 * 
	 * @var string 
	 */
	const EXPIRE = 'expires';

	/**
	 * 执行设置操作
	 * 
	 * @param string $key 缓存数据的唯一key
	 * @param string $value 缓存数据值，该值是一个含有有效数据的序列化的字符串
	 * @param int $expires 缓存数据保存的有效时间，单位为秒，默认时间为0即永不过期
	 * @return boolean
	 * @throws WindException 缓存失败的时候抛出异常
	 */
	protected abstract function setValue($key, $value, $expires = 0);

	/**
	 * 执行添加操作 
	 * 
	 * 当数据key不存在的时候添加，如果key已经存在则添加失败
	 * 
	 * @param string $key 缓存数据的唯一key
	 * @param string $value 缓存数据值，该值是一个含有有效数据的序列化的字符串
	 * @param int $expires 缓存数据保存的有效时间，单位为秒，默认时间为0即永不过期
	 * @return boolean
	 * @throws WindException 缓存失败的时候抛出异常
	 */
	protected abstract function addValue($key, $value, $expires = 0);

	/**
	 * 执行获取操作
	 * 
	 * @param string $key 缓存数据的唯一key
	 * @return string  缓存的数据
	 * @throws WindException 缓存数据获取失败抛出异常
	 */
	protected abstract function getValue($key);

	/**
	 * 需要实现的删除操作
	 * 
	 * @param string $key 需要删除的缓存数据的key
	 * @return boolean
	 */
	protected abstract function deleteValue($key);

	/**
	 * 清楚缓存，过期及所有缓存
	 * 
	 * @return boolean
	 */
	public abstract function clear();

	/**
	 * 设置缓存
	 * 如果key不存在，添加缓存；否则，将会替换已有key的缓存。
	 * 
	 * @param string $key 保存缓存数据的键。
	 * @param string $value 保存缓存数据。
	 * @param int $expires 缓存数据的过期时间,0表示永不过期
	 * @param IWindCacheDependency $denpendency 缓存依赖
	 * @return boolean
	 * @throws WindCacheException 缓存失败时抛出异常
	 */
	public function set($key, $value, $expires = 0, IWindCacheDependency $dependency = null) {
		try {
			$expires = $expires ? $expires : $this->getExpire();
			$data = $this->buildData($value, $expires, $dependency);
			return $this->setValue($this->buildSecurityKey($key), $data, $expires);
		} catch (Exception $e) {
			throw new WindCacheException('[cache.AbstractWindCache.set] Setting cache failed.' . $e->getMessage());
		}
	}

	/**
	 * 增加一个条目到缓存服务器
	 * 
	 * 方法在缓存服务器之前不存在key时， 以key作为key存储一个变量var到缓存服务器。
	 * <note><b>注意：</b>新的元素的值不会小于0。 并且在元素不存在时不能创建它</note> 
	 *
	 * @param string $key 保存缓存数据的键。
	 * @param string $value 保存缓存数据。
	 * @param int $expires 缓存数据的过期时间,0表示永不过期
	 * @param IWindCacheDependency $denpendency 缓存依赖
	 * @return boolean 成功时返回 TRUE， 或者在失败时返回 FALSE. 如果这个key已经存在返回FALSE。
	 */
	public function add($key, $value, $expires = 0, IWindCacheDependency $dependency = null) {
		try {
			$key = $this->buildSecurityKey($key);
			$data = $this->getValue($key);
			if ($data) return false;
			$expires = $expires ? $expires : $this->getExpire();
			$data = $this->buildData($value, $expires, $dependency);
			return $this->addValue($key, $data, $expires);
		} catch (Exception $e) {
			throw new WindCacheException('[cache.AbstractWindCache.set] Setting cache failed.' . $e->getMessage());
		}
	}

	/**
	 * 根据缓存key获取指定缓存
	 * 
	 * @param string $key 获取缓存数据的标识,即键
	 * @return mixed 返回被缓存的数据
	 * @throws WindCacheException 获取失败时抛出异常
	 */
	public function get($key) {
		try {
			return $this->formatData($key, $this->getValue($this->buildSecurityKey($key)));
		} catch (Exception $e) {
			throw new WindCacheException('[cache.AbstractWindCache.get] Getting cache data failed. (' . $e->getMessage() . ')');
		}
	}

	/**
	 * 通过key批量获取缓存数据
	 * 
	 * @param array $keys 批量缓存的key集合
	 * @return array 返回批量key对应的缓存数据组成的缓存数据数组
	 */
	public function batchGet(array $keys) {
		$data = array();
		foreach ($keys as $key) {
			$data[$key] = $this->get($key);
		}
		return $data;
	}

	/**
	 * 删除缓存数据
	 * 
	 * @param string $key 获取缓存数据的标识，即键
	 * @return boolean
	 * @throws WindCacheException 删除失败时抛出异常
	 */
	public function delete($key) {
		try {
			return $this->deleteValue($this->buildSecurityKey($key));
		} catch (Exception $e) {
			throw new WindCacheException('[cache.AbstractWindCache.delete] Delete cache data failed. (' . $e->getMessage() . ')');
		}
	}

	/**
	 * 通过key批量删除缓存数据
	 * 
	 * @param array $keys 需要批量删除的缓存key组成的组数
	 * @return boolean 
	 */
	public function batchDelete(array $keys) {
		foreach ($keys as $key)
			$this->delete($key);
		return true;
	}

	/**
	 * 将指定元素的值增加value
	 * 
	 * 如果指定的key 对应的元素不是数值类型并且不能被转换为数值， 会将此值修改为value.
	 * <note><b>注意：</b>不会在key对应元素不存在时创建元素。</note> 
	 *
	 * @param string $key 将要增加值的元素的key。 
	 * @param int $step 参数value表明要将指定元素值增加多少。 
	 * @return int 成功时返回新的元素值失败时返回false。 
	 */
	public function increment($key, $step = 1) {
		$data = $this->get($key);
		if (!is_numeric($data)) return false;
		$data += intval($step);
		$this->set($key, $data);
		return $data;
	}

	/**
	 * 将元素的值减小value
	 * 
	 * 如果指定的key 对应的元素不是数值类型并且不能被转换为数值， 会将此值修改为value.
	 * <note><b>注意：</b>新的元素的值不会小于0。 并且在元素不存在时不能创建它</note> 
	 *
	 * @param string $key 将要增加值的元素的key。 
	 * @param int $step value参数指要将指定元素的值减小多少。 
	 * @return int 成功的时候返回元素的新值，失败的时候返回false。 
	 */
	public function decrement($key, $step = 1) {
		$data = $this->get($key);
		if (!is_numeric($data)) return false;
		$data -= intval($step);
		$data < 0 && $data = 0;
		$this->set($key, $data);
		return $data;
	}

	/**
	 * 构造保存的数据
	 *
	 * @param string $value 保存缓存数据。
	 * @param int $expires 缓存数据的过期时间,0表示永不过期
	 * @param IWindCacheDependency $denpendency 缓存依赖
	 * @return array
	 */
	protected function buildData($value, $expires = 0, IWindCacheDependency $dependency = null) {
		$data = array(
			self::DATA => $value, 
			self::EXPIRE => $expires, 
			self::STORETIME => time(), 
			self::DEPENDENCY => null, 
			self::DEPENDENCYCLASS => '');
		if (null !== $dependency) {
			$dependency->injectDependent(self::EXPIRE);
			$data[self::DEPENDENCY] = $dependency;
			$data[self::DEPENDENCYCLASS] = get_class($dependency);
		}
		return serialize($data);
	}

	/**
	 * 格式化输出
	 * 
	 * 将从缓存对象中获得的缓存源数据进行格式化输出.该源数据是一个格式良好的数组的序列化字符串,需要反序列化获得源数组.
	 * 如果没有数据,则返回false
	 * 如果含有数据,则返回该数据
	 * 
	 * @param string $key  缓存的key值
	 * @param string $value 缓存的数据的序列化值
	 * @return mixed 返回保存的真实数据,如果没有数值则返回false  
	 */
	protected function formatData($key, $value) {
		if (!$value) return false;
		$data = unserialize($value);
		return $this->hasChanged($key, $data) ? false : $data[self::DATA];
	}

	/**
	 * 判断数据是否已经被更新
	 * 
	 * 如果缓存中有数据,则检查缓存依赖是否已经变更,如果变更则删除缓存,并且返回true.
	 * 如果没有更新则返回false.
	 * 
	 * @param string $key 缓存的key
	 * @param array  $data 缓存中的数据
	 * @return boolean true表示缓存已变更,false表示缓存未变改
	 */
	protected function hasChanged($key, array $data) {
		if ($data[self::DEPENDENCY]) {
			$dependency = $data[self::DEPENDENCY];
			if (!$dependency->hasChanged($this, $key, $data[self::EXPIRE])) return false;
		} elseif ($data[self::EXPIRE]) {
			$_overTime = $data[self::EXPIRE] + $data[self::STORETIME];
			if ($_overTime >= time()) return false;
		} else
			return false;
		$this->delete($key);
		return true;
	}

	/**
	 * 将数据key计算生成安全的key
	 * 
	 * @param string $key 真实的缓存key
	 * @return string 加入安全码计算之后返回的保存key
	 */
	protected function buildSecurityKey($key) {
		$this->keyPrefix && $key = $this->keyPrefix . '_' . $key;
		return $key . $this->getSecurityCode();
	}

	/**
	 * 返回缓存Key值前缀
	 * 
	 * 默认值为null无任何前缀添加
	 * 
	 * @return string $prefix 返回key前缀
	 */
	protected function getKeyPrefix() {
		return $this->keyPrefix;
	}

	/**
	 * 设置key前缀
	 * 
	 * @param sting $keyPrefix key的前缀
	 */
	public function setKeyPrefix($keyPrefix) {
		$this->keyPrefix = $keyPrefix;
	}

	/**
	 * 获得缓存key计算中加入的安全码
	 * 
	 * @return string $securityCode
	 */
	protected function getSecurityCode() {
		return $this->securityCode;
	}

	/**
	 * 设置缓存key计算中加入的安全码
	 * 
	 * @param string $securityCode 安全码
	 */
	public function setSecurityCode($securityCode) {
		$this->securityCode = $securityCode;
	}

	/**
	 * 返回过期时间设置
	 * 
	 * 单位为秒，默认值为0永不过期
	 * 
	 * @return int $expire 缓存过期时间，默认为0永不过期，单位为秒
	 */
	public function getExpire() {
		return $this->expire;
	}

	/**
	 * 设置缓存过期时间
	 * 
	 * 单位为秒,默认为0永不过期
	 * 
	 * @param int $expire 缓存过期时间,单位为秒,默认为0永不过期
	 */
	public function setExpire($expire) {
		$this->expire = intval($expire);
	}

	/**
	 * 设置配置信息
	 * 
	 * 支持三个配置项: 
	 * <ul>
	 * <li><i>security-code</i>: 安全码配置</li>
	 * <li><i>key-prefix</i>: key前缀设置</li>
	 * <li><i>expires</i>: 缓存过期时间配置,单位为秒,默认为0</li>
	 * </ul>
	 * 
	 * @param array $config 缓存配置信息
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		$this->setSecurityCode($this->getConfig('security-code', '', ''));
		$this->setKeyPrefix($this->getConfig('key-prefix', '', ''));
		$this->setExpire($this->getConfig('expires', '', 0));
	}
}