<?php
Wind::import('WIND:cache.AbstractWindCache');
Wind::import('WIND:utility.WindFile');
/**
 * file缓存策略实现
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
 * 'dir' => 'data',	//缓存文件存放的目录,注意可读可写
 * 'suffix' => 'txt',	//缓存文件的后缀,默认为txt后缀
 * 'dir-level' => '0',	//缓存文件存放目录的子目录长度,默认为0不分子目录
 * 'security-code' => '',	//继承自AbstractWindCache,安全码配置
 * 'key-prefix' => '', //继承自AbstractWindCache,缓存key前缀
 * 'expires' => '0',	//继承自AbstractWindCache,缓存过期时间配置
 * )
 * </code>
 * <i>使用方法:</i><br/>
 * 1、您可以像使用普通类库一样使用该组件:
 * <code>
 * Wind::import('WIND:cache.strategy.WindFileCache');
 * $cache = new WindFileCache();
 * $cache->setConfig(array('dir' => 'data', 'suffix' => 'php'));
 * $cache->set('name', 'fileCacheTest');
 * </code>
 * 2、采用组件配置的方式，通过组件机制调用
 * 在应用配置的components组件配置块中,配置fileCache(<i>该名字将决定调用的时候使用的组件名字</i>):
 * <pre>
 * 'fileCache' => array(
 * 'path' => 'WIND:cache.strategy.WindFileCache',
 * 'scope' => 'singleton',
 * 'config' => array(
 * 'dir' => 'data',
 * 'suffix' => 'txt',
 * 'dir-level' => '0',
 * 'security-code' => '',
 * 'key-prefix' => '',
 * 'expires' => '0',
 * ),
 * ),
 * </pre>
 * 在应用中可以通过如下方式获得dbCache对象:
 * <code>
 * $fileCache = Wind::getComponent('fileCache'); //dbCache的名字来自于组件配置中的名字
 * </code>
 * the last known user to change this file in the repository <LastChangedBy:
 * xiaoxiao >
 * 
 * @author xiaoxiao <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindFileCache.php 3791 2012-10-30 04:01:29Z liusanbian $
 * @package strategy
 */
class WindFileCache extends AbstractWindCache {
	
	/**
	 * 缓存目录
	 * 
	 * @var string
	 */
	private $cacheDir;
	
	/**
	 * 缓存后缀
	 * 
	 * @var string
	 */
	private $cacheFileSuffix = 'txt';
	
	/**
	 * 缓存子目录的长度
	 * 
	 * @var int
	 */
	private $cacheDirectoryLevel = 0;
	
	/**
	 * 保存缓存目录列表
	 * 如果用户已经访问过统一个缓存，则会直接从该列表中获取该具体值而不重新计算。
	 * 
	 * @var array
	 */
	private $cacheFileList = array();
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::setValue()
	 */
	protected function setValue($key, $value, $expire = 0) {
		return WindFile::write($key, $value) == strlen($value);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::addValue()
	 */
	protected function addValue($key, $value, $expire = 0) {
		return WindFile::write($key, $value) == strlen($value);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::get()
	 */
	protected function getValue($key) {
		if (!is_file($key)) return null;
		return WindFile::read($key);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::deleteValue()
	 */
	protected function deleteValue($key) {
		return WindFile::write($key, '');
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::clear()
	 */
	public function clear() {
		return WindFolder::clearRecur($this->getCacheDir());
	}

	/**
	 * 根据用户key计算获取真是缓存文件
	 * 缓存key在安全处理之后,判断该key是否已经被访问过
	 * <ul>
	 * <li>如果被访问过,则直接返回该真实缓存文件</li>
	 * <li>没有访问过,则将会进入计算流程.
	 * <ol>
	 * <li>如果用该组件配置了缓存子目录的长度n：
	 * <ul>
	 * <li>获得缓存key的md5值的0~n的子字串作为子缓存目录;</li>
	 * <li>将缓存文件存放在该缓存子目录下.同时将该缓存文件的新路径保存到已访问的缓存路径列表中,供下次直接调用.</li>
	 * </ul>
	 * </li>
	 * <li>如果没有配置缓存子目录长度,则直接将该文件缓存在缓存根目录下,同时也将该缓存文件路径保存在已访问的缓存路径列表中.</li>
	 * </ol>
	 * </li>
	 * </ul>
	 * 
	 * @param string $key 用户的缓存文件key
	 * @return string 真实的缓存文件
	 */
	protected function buildSecurityKey($key) {
		$key = parent::buildSecurityKey($key);
		if (false !== ($dir = $this->checkCacheDir($key))) return $dir;
		$_dir = $this->getCacheDir();
		if (0 < ($level = $this->getCacheDirectoryLevel())) {
			$_subdir = substr(md5($key), 0, $level);
			$_dir .= '/' . $_subdir;
			WindFolder::isDir($_dir) || WindFolder::mk($_dir);
		}
		$filename = $key . '.' . $this->getCacheFileSuffix();
		$this->cacheFileList[$key] = ($_dir ? $_dir . '/' . $filename : $filename);
		return $this->cacheFileList[$key];
	}

	/**
	 * 是否缓存key已经存在缓存访问列表中
	 * <ul>
	 * <li>如果缓存key已经在缓存访问列表中,则将会直接返回存在的值</li>
	 * <li>如果不存在则返回false.</li>
	 * </ul>
	 * 
	 * @param string $key 待检查的缓存key
	 * @return string boolean
	 */
	private function checkCacheDir($key) {
		return isset($this->cacheFileList[$key]) ? $this->cacheFileList[$key] : false;
	}

	/**
	 * 设置缓存目录
	 * 
	 * @param string $dir 缓存目录，必须是<b>可写可读</b>权限
	 */
	public function setCacheDir($dir) {
		$_dir = Wind::getRealPath($dir, false, true);
		WindFolder::mkRecur($_dir);
		$this->cacheDir = realpath($_dir);
	}

	/**
	 * 获得缓存目录
	 * 
	 * @return string $cacheDir 返回配置的缓存目录
	 */
	private function getCacheDir() {
		return $this->cacheDir;
	}

	/**
	 * 设置缓存文件的后缀
	 * 
	 * @param string $cacheFileSuffix 缓存文件的后缀，默认为txt
	 */
	public function setCacheFileSuffix($cacheFileSuffix) {
		$this->cacheFileSuffix = $cacheFileSuffix;
	}

	/**
	 * 获得缓存文件的后缀
	 * 
	 * @return string $cacheFileSuffix 缓存文件的后缀
	 */
	private function getCacheFileSuffix() {
		return $this->cacheFileSuffix;
	}

	/**
	 * 设置缓存存放的目录下子目录的长度
	 * 
	 * @param int $cacheDirectoryLevel
	 *        该值将会决定缓存目录下子缓存目录的长度，最小为0（不建子目录），最大为32（md5值最长32），缺省为0
	 */
	public function setCacheDirectoryLevel($cacheDirectoryLevel) {
		$this->cacheDirectoryLevel = $cacheDirectoryLevel;
	}

	/**
	 * 返回缓存存放的目录下子目录的长度
	 * 该值将会决定缓存目录下子缓存目录的长度，最小为0（不建子目录），最大为32（md5值最长32），缺省为0
	 * 
	 * @return int $cacheDirectoryLevel
	 */
	public function getCacheDirectoryLevel() {
		return $this->cacheDirectoryLevel;
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindCache::setConfig()
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		$this->setCacheDir($this->getConfig('dir'));
		$this->setCacheFileSuffix($this->getConfig('suffix', '', 'txt'));
		$this->setCacheDirectoryLevel($this->getConfig('dir-level', '', 0));
	}
}