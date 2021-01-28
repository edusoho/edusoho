<?php

namespace Biz\System\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\CacheService;

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

    public function gets(array $names)
    {
        $this->garbageCollection();
        $names = array_filter($names);
        if (empty($names)) {
            return array();
        }

        $datas = array();
        $caches = $this->getCacheDao()->findByNames($names);
        $now = time();
        foreach ($caches as $cache) {
            if ($cache['expiredTime'] > 0 && $cache['expiredTime'] < $now) {
                continue;
            }
            $datas[$cache['name']] = $cache['serialized'] ? unserialize($cache['data']) : $cache['data'];
        }

        return $datas;
    }

    public function set($name, $data, $expiredTime = 0)
    {
        $serialized = is_string($data) ? 0 : 1;

        $cache = array(
            'name' => $name,
            'data' => $serialized ? serialize($data) : $data,
            'serialized' => $serialized,
            'expiredTime' => $expiredTime,
            'createdTime' => time(),
        );

        $cached = $this->getCacheDao()->findByNames(array($name));
        if (empty($cached)) {
            return $this->getCacheDao()->create($cache);
        } else {
            return $this->getCacheDao()->updateByName($name, $cache);
        }
    }

    public function clear($name = null)
    {
        if (!empty($name)) {
            return $this->getCacheDao()->deleteByName($name);
        } else {
            return $this->getCacheDao()->deleteAll();
        }
    }

    /**
     * @todo
     */
    protected function garbageCollection()
    {
    }

    protected function getCacheDao()
    {
        return $this->createDao('System:CacheDao');
    }
}
