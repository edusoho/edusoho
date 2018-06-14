<?php

namespace Tests\Unit\System\Service;

use Biz\BaseTestCase;

class CacheServiceTest extends BaseTestCase
{
    public function testSet()
    {
        $foundCache = $this->getCacheService()->set('name', 'data', 1000);
        $this->assertEquals('name', $foundCache['name']);
        $this->assertEquals('data', $foundCache['data']);
        $this->assertEquals(0, $foundCache['serialized']);
        $this->assertEquals(1000, $foundCache['expiredTime']);

        $time = time();
        $foundCache = $this->getCacheService()->set('name', 100, $time);
        $this->assertEquals('name', $foundCache['name']);
        $this->assertEquals('i:100;', $foundCache['data']);
        $this->assertEquals(1, $foundCache['serialized']);
        $this->assertEquals($time, $foundCache['expiredTime']);
    }

    public function testGet()
    {
        $this->getCacheService()->set('name1', 'data');
        $this->getCacheService()->set('name2', 'data', 1000);
        $foundCacheName = $this->getCacheService()->get('name1');
        $this->assertEquals('data', $foundCacheName);

        $foundCacheName = $this->getCacheService()->get('xxxx');
        $this->assertNull($foundCacheName);

        $foundCacheName = $this->getCacheService()->get('name2');
        $this->assertNull($foundCacheName);
    }

    public function testGets()
    {
        $this->getCacheService()->set('name1', 'data1', 1000);
        $this->getCacheService()->set('name2', 'data2', 2000);
        $this->getCacheService()->set('name3', 'data3', strtotime('+1 day'));
        $foundCacheNames = $this->getCacheService()->gets(array('name1', 'name2', 'name3', 'aaa', 'bbb'));
        $this->assertEquals(1, count($foundCacheNames));
        $this->assertContains('name3', array_keys($foundCacheNames));
    }

    /**
     * @group clear
     */
    public function testClear()
    {
        $this->getCacheService()->set('name1', 'data1', 1000);
        $this->getCacheService()->set('name2', 'data2');
        $this->getCacheService()->set('name3', 'data3', time());

        $cache = $this->getCacheService()->clear('name1');
        $foundCache = $this->getCacheService()->get('name1');
        $this->assertNull($foundCache);

        $cache = $this->getCacheService()->clear('name2');
        $foundCache = $this->getCacheService()->get('name2');
        $this->assertNull($foundCache);

        $cache = $this->getCacheService()->clear('name3');
        $foundCache = $this->getCacheService()->get('name3');
        $this->assertNull($foundCache);
    }

    /**
     * @group clear
     */
    public function testClearAll()
    {
        $this->getCacheService()->set('name1', 'data1', 1000);
        $this->getCacheService()->set('name2', 'data2');
        $this->getCacheService()->set('name3', 'data3', time());
        $cache = $this->getCacheService()->clear();
        $foundCacheNames = $this->getCacheService()->gets(array('name1', 'name2', 'name3'));
        $this->assertEmpty($foundCacheNames);
    }

    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }
}
