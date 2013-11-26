<?php
Wind::import('WIND:parser.IWindConfigParser');
/**
 * 配置文件解析类
 * 
 * 配置文件格式允许有4中格式：xml, php, properties, ini
 * 
 * 根据用户传入的配置文件所在位置解析配置文件，
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindConfigParser.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package parser
 */
class WindConfigParser implements IWindConfigParser {
	const CONFIG_XML = '.XML';
	const CONFIG_PHP = '.PHP';
	const CONFIG_INI = '.INI';
	const CONFIG_PROPERTIES = '.PROPERTIES';
	private $configs = array();
	
	/* (non-PHPdoc)
     * @see IWindConfigParser::parse()
     */
	public function parse($configPath, $alias = '', $append = '', AbstractWindCache $cache = null) {
		if ($alias && $cache && ($config = $this->getCache($alias, $append, $cache))) return $config;
		if (!is_file($configPath)) throw new WindException(
			'[parser.WindConfigParser.parse] The file \'' . $configPath . '\' is not exists');
		$ext = strtoupper(strrchr($configPath, '.'));
		$config = ($ext == self::CONFIG_PHP) ? @include ($configPath) : $this->createParser($ext)->parse($configPath);
		if ($alias && $cache) $this->setCache($alias, $append, $cache, $config);
		return $config;
	}

	/**
	 * 设置配置缓存
	 * 
	 * $alias和$append的关系如下:
	 * <ul>
	 * <li>如果没有设置$alias或是没有设置$cache，则将不保存数据</li>
	 * <li>如果没有设置$append: 则将会以$alias为名将$data保存在缓存$cache中</li>
	 * <li>如果设置了$append和$alias: 则先去从$cache中获得名为$append的缓存内容，并且将$data以$alias为键名保存到该缓存内容中,
	 * 然后仍然以$append之名写回到$cache中</li>
	 * </ul>
	 * 
	 * @param string $alias 配置文件的缓存别名
	 * @param string $append 配置文件的追加的配置
	 * @param AbstractWindCache $cache 配置文件使用的缓存介质
	 * @return void
	 */
	private function setCache($alias, $append, $cache, $data) {
		if ($append) {
			$this->configs[$alias] = $data;
			$cache->set($append, $this->configs);
		} else {
			$cache->set($alias, $data);
		}
	}

	/**
	 * 返回配置缓存
	 * 
	 * @param string $alias 配置保存用的名字
	 * @param string $append 配置追加的配置
	 * @param AbstractWindCache $cache 配置保存的缓存介质
	 * @return array
	 */
	private function getCache($alias, $append, $cache) {
		if (!$append) return $cache->get($alias);
		if (isset($this->configs[$alias])) return $this->configs[$alias];
		$this->configs = $cache->get($append);
		return isset($this->configs[$alias]) ? $this->configs[$alias] : array();
	}

	/**
	 * 创建配置文件解析器
	 * 
	 * @param string $type 配置文件的类型
	 * @return object 解析器
	 */
	private function createParser($type) {
		switch ($type) {
			case self::CONFIG_XML:
				Wind::import("WIND:parser.WindXmlParser");
				return new WindXmlParser();
			case self::CONFIG_INI:
				Wind::import("WIND:parser.WindIniParser");
				return new WindIniParser();
			case self::CONFIG_PROPERTIES:
				Wind::import("WIND:parser.WindPropertiesParser");
				return new WindPropertiesParser();
			default:
				throw new WindException('[parser.WindConfigParser.createParser] \'ConfigParser\' failed to initialize.');
		}
	}
}