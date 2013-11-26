<?php
Wind::import('WIND:cache.AbstractWindCache');

/**
 * 文件缓存实现
 *	明文缓存
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: codetemplates(windframework_docs_zend_8.0).xml 2781 2011-09-22 03:59:17Z yishuo $
 * @package wind
 */
class PwFileCache extends AbstractWindCache {
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
	 *
	 * 如果用户已经访问过统一个缓存，则会直接从该列表中获取该具体值而不重新计算。
	 *
	 * @var array
	 */
	private $cacheFileList = array();
	
	/* (non-PHPdoc)
	 * @see AbstractWindCache::setValue()
	 */
	protected function setValue($key, $value, $expires = 0) {
		return WindFile::savePhpData($key, $value, 'w');
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindCache::addValue()
	 */
	protected function addValue($key, $value, $expires = 0) {
		return WindFile::savePhpData($key, $value, 'w');
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindCache::getValue()
	 */
	protected function getValue($key) {
		if (!is_file($key)) return null;
		return include $key;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindCache::deleteValue()
	 */
	protected function deleteValue($key) {
		return WindFile::savePhpData($key, '', 'w');
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindCache::clear()
	 */
	public function clear() {
		return WindFolder::clearRecur($this->getCacheDir());
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindCache::buildData()
	 */
	protected function buildData($value, $expires = 0, IWindCacheDependency $dependency = null) {
		$data = array(
			self::DATA => $value,
			self::EXPIRE => $expires ? $expires + time() : 0,
			);
		return $data;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindCache::buildSecurityKey()
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
	
	/* (non-PHPdoc)
	 * @see AbstractWindCache::formatData()
	 */
	protected function formatData($key, $value) {
		if (!$value) return false;
		if (!$value[self::EXPIRE] || $value[self::EXPIRE] >= time()) {
			return $value[self::DATA];
		}
		$this->delete($key);
		return false;
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
	 * @param int $cacheDirectoryLevel 该值将会决定缓存目录下子缓存目录的长度，最小为0（不建子目录），最大为32（md5值最长32），缺省为0
	 */
	public function setCacheDirectoryLevel($cacheDirectoryLevel) {
		$this->cacheDirectoryLevel = $cacheDirectoryLevel;
	}
	
	/**
	 * 返回缓存存放的目录下子目录的长度
	 *
	 * 该值将会决定缓存目录下子缓存目录的长度，最小为0（不建子目录），最大为32（md5值最长32），缺省为0
	 *
	 * @return int $cacheDirectoryLevel
	 */
	public function getCacheDirectoryLevel() {
		return $this->cacheDirectoryLevel;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindCache::setConfig()
	*/
	public function setConfig($config) {
		parent::setConfig($config);
		$this->setCacheDir($this->getConfig('dir'));
		$this->setCacheFileSuffix($this->getConfig('suffix', '', 'txt'));
		$this->setCacheDirectoryLevel($this->getConfig('dir-level', '', 0));
	}

	/**
	 * 是否缓存key已经存在缓存访问列表中
	 *
	 * <ul>
	 * <li>如果缓存key已经在缓存访问列表中,则将会直接返回存在的值</li>
	 * <li>如果不存在则返回false.</li>
	 * </ul>
	 *
	 * @param string $key  待检查的缓存key
	 * @return string|boolean 如果存在则返回被保存的值，如果不存在则返回false;
	 */
	private function checkCacheDir($key) {
		return isset($this->cacheFileList[$key]) ? $this->cacheFileList[$key] : false;
	}
}