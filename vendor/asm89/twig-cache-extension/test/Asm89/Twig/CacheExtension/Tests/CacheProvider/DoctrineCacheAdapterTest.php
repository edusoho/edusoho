<?php

/*
 * This file is part of twig-cache-extension.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\CacheExtension\Tests\CacheProvider;

use Asm89\Twig\CacheExtension\CacheProvider\DoctrineCacheAdapter;

class DoctrineCacheAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $cacheMock = $this->createCacheMock();
        $cacheMock->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue('fromcache'));

        $cache = new DoctrineCacheAdapter($cacheMock);

        $this->assertEquals('fromcache', $cache->fetch('test'));
    }

    public function testSave()
    {
        $cacheMock = $this->createCacheMock();
        $cacheMock->expects($this->once())
            ->method('save')
            ->with('key', 'value', 42);

        $cache = new DoctrineCacheAdapter($cacheMock);

        $cache->save('key', 'value', 42);
    }

    public function createCacheMock()
    {
        return $this->getMock('Doctrine\Common\Cache\Cache');
    }
}
