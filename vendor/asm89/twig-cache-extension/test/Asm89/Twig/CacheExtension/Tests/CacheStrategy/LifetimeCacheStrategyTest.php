<?php

/*
 * This file is part of twig-cache-extension.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\CacheExtension\Tests\CacheStrategy;

use Asm89\Twig\CacheExtension\CacheStrategy\LifetimeCacheStrategy;

class LifetimeCacheStrategyTest extends \PHPUnit_Framework_TestCase
{
    private $cacheProviderMock;

    public function createCacheStrategy()
    {
        $this->cacheProviderMock = $this->createCacheProviderMock();

        return new LifetimeCacheStrategy($this->cacheProviderMock);
    }

    public function testGenerateKeyUsesGivenLifetime()
    {
        $strategy = $this->createCacheStrategy();

        $key = $strategy->generateKey('v42', 42);

        $this->assertEquals(42, $key['lifetime']);
    }

    public function testGenerateKeyAnnotatesKey()
    {
        $strategy = $this->createCacheStrategy();

        $key = $strategy->generateKey('the_annotation', 42);

        $this->assertContains('the_annotation', $key['key']);
    }

    /**
     * @dataProvider getInvalidLifetimeValues
     * @expectedException \Asm89\Twig\CacheExtension\Exception\InvalidCacheLifetimeException
     */
    public function testGenerateKeyThrowsExceptionWhenNoLifetimeProvided($value)
    {
        $strategy = $this->createCacheStrategy();

        $strategy->generateKey('v42', $value);
    }

    public function getInvalidLifetimeValues()
    {
        return array(
            array('foo'),
            array(array('foo')),
        );
    }

    public function createCacheProviderMock()
    {
        return $this->createMock('Asm89\Twig\CacheExtension\CacheProviderInterface');
    }
}
