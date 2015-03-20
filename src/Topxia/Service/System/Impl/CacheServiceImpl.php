<?php
namespace Topxia\Service\System\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\System\CacheService;

class CacheServiceImpl extends BaseService implements CacheService
{

    public function get($name)
    {
    	$datas = $this->gets(array($name));
        if (empty($datas)) {
            return null;
        }
    	return reset($datas);
    }

    public function gets (array $names)
    {
        $this->garbageCollection();
    	$names = array_filter($names);
    	if (empty($names)) {
    		return array();
    	}

    	$datas = array();
    	$caches = $this->getCacheDao()->findCachesByNames($names);
    	$now = time();
    	foreach ($caches as $cache) {
    		if ($cache['expiredTime'] > 0 && $cache['expiredTime'] < $now ) {
    			continue;
    		}
    		$datas[$cache['name']] = $cache['serialized'] ? unserialize($cache['data']) : $cache['data'];
    	}
    	return $datas;
    }

    public function set($name, $data, $expiredTime = 0)
    {
		$this->getCacheDao()->deleteCacheByName($name);

    	$serialized = is_string($data) ? 0 : 1;

    	$cache = array(
    		'name' => $name,
    		'data' => $serialized ? serialize($data) : $data,
    		'serialized' => $serialized,
    		'expiredTime' => $expiredTime,
    		'createdTime' => time(),
		);

		return $this->getCacheDao()->addCache($cache);
    }

    public function clear ($name = NULl)
    {
    	if (!empty($name)) {
    		return $this->getCacheDao()->deleteCacheByName($name);
    	} else {
    		return $this->getCacheDao()->deleteAllCache();
    	}
    }

    /**
     * @todo
     */
    protected function garbageCollection()
    {

    }

    protected function getCacheDao ()
    {
        return $this->createDao('System.CacheDao');
    }

}