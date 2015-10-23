<?php
Wind::import('WIND:cache.AbstractWindCache');
/**
 * WindEacceleratorCache实现Eaccelerator动态内容缓存功能。
 * 
 * Eaccelerator是一款php加速器、优化器、编码器及动态内容缓存。
 * 提供对方访问接口如下:
 * <ul>
 *   <li>set($key, $value, $expire): 继承自{@link AbstractWindCache::set()}.</li>
 *   <li>get($key): 继承自{@link AbstractWindCache::get()}.</li>
 *   <li>delete($key): 继承自{@link AbstractWindCache::delete()}.</li>
 *   <li>batchGet($keys): 继承自{@link AbstractWindCache::batchGet()}.</li>
 *   <li>batchDelete($keys): 继承自{@link AbstractWindCache::batchDelete()}.</li>
 *   <li>setConfig($config): 继承自{@link AbstractWindCache::setConfig()}.</li>
 * </ul>
 * 该缓存策略从AbstractWindCache类中继承三个配置项：
 * <code>
 *  array(
 *  	'security-code' => '',	//继承自AbstractWindCache,安全码配置
 * 		'key-prefix' => '',		//继承自AbstractWindCache,缓存key前缀
 *      'expires' => '0',	//继承自AbstractWindCache,缓存过期时间配置
 *  )
 * </code>
 * <i>使用方式:</i><br/>
 * 1、您可以像使用普通类库一样使用该组件:
 * <code>
 * Wind::import('WIND:cache.strategy.WindEacceleratorCache');
 * $cache = new WindEacceleratorCache();
 * $cache->set('name', 'xxx');
 * </code>
 * 2、同时您也可以使用组件配置的方式实现调用,在应用配置中的组件配置块(components),配置该组件命名为eacceleratorCache如下：
 * <code>
 *  'eacceleratorCache' => array(
 *		'path' => 'WIND:cache.strategy.WindEacceleratorCache',
 *		'scope' => 'singleton',
 *		'config' => array(
 *			'security-code' => '', 
 * 	    	'key-prefix' => '',
 *      	'expires' => '0',
 *		),
 *	),
 * </code>
 * 在应用中可以通过如下方式实现访问:
 * <code>
 * $cache = Wind::getComponent('eacceleratorCache');
 * $cache->set('name', 'cacheTest');
 * </code>
 * 关于组件配置的相关说明请参考组件配置一章.
 * 
 * <note><b>注意：</b>要使用EacceleratorCache组件，需要安装eaccelerator扩展支持。</note>
 * 
 * the last known user to change this file in the repository  <LastChangedBy: xiaoxiao >
 * @author xiaoxiao <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindEacceleratorCache.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package strategy
 */
class WindEacceleratorCache extends AbstractWindCache {

	/**
	 * 构造函数
	 * 
	 * 判断是否有安装eaccelerator扩展,如果没有安装则会抛出WindCacheException异常
	 * 
	 * @throws WindCacheException
	 */
	public function __construct() {
		if (!function_exists('eaccelerator_get')) {
			throw new WindCacheException('[cache.strategy.WindEacceleratorCache] The eaccelerator extension must be loaded !');
		}
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::setValue()
	 */
	protected function setValue($key, $value, $expire = 0) {
		return eaccelerator_put($key, $value, $expire);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindCache::addValue()
	 */
	protected function addValue($key, $value, $expire = 0) {
		return eaccelerator_put($key,$value,$expire);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::get()
	 */
	protected function getValue($key) {
		return eaccelerator_get($key);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::deleteValue()
	 */
	protected function deleteValue($key) {
		return eaccelerator_rm($key);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::clear()
	 */
	public function clear() {
		return eaccelerator_gc();
	}
}