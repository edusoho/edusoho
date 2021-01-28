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

use Asm89\Twig\CacheExtension\CacheStrategy\IndexedChainingCacheStrategy;

class IndexedChainingCacheStrategyTest extends \PHPUnit_Framework_TestCase
{
    private $cacheStrategyMocks = array();

    public function createCacheStrategy()
    {
        foreach($this->getStrategies() as $config) {
            list($key, $return) = $config;

            $cacheStrategyMock = $this->createCacheStrategyMock();
            $cacheStrategyMock->expects($this->any())
                ->method('generateKey')
                ->will($this->returnValue($return));

            $this->cacheStrategyMocks[$key] = $cacheStrategyMock;
        }

        return new IndexedChainingCacheStrategy($this->cacheStrategyMocks);
    }

    /**
     * @dataProvider getStrategies
     */
    public function testGenerateKeyProxiesToAppropriateStrategy($key, $return)
    {
        $strategy = $this->createCacheStrategy();

        $generatedKey = $strategy->generateKey('v42', array($key => 'proxied_value'));

        $this->assertEquals($return, $generatedKey['key']);
        $this->assertEquals($key, $generatedKey['strategyKey']);
    }

    /**
     * @expectedException \Asm89\Twig\CacheExtension\Exception\NonExistingStrategyKeyException
     */
    public function testGenerateKeyThrowsExceptionOnMissingKey()
    {
        $strategy = $this->createCacheStrategy();
        $strategy->generateKey('v42', 'proxied_value');
    }

    /**
     * @expectedException \Asm89\Twig\CacheExtension\Exception\NonExistingStrategyException
     * @expectedExceptionMessage No strategy configured with key "unknown"
     */
    public function testGenerateKeyThrowsExceptionOnUnknownKey()
    {
        $strategy = $this->createCacheStrategy();
        $strategy->generateKey('v42', array('unknown' => 'proxied_value'));
    }

    public function getStrategies()
    {
        return array(
            array('foo', 'foo_key'),
            array('bar', 'bar_key'),
        );
    }

    public function createCacheStrategyMock()
    {
        return $this->createMock('Asm89\Twig\CacheExtension\CacheStrategyInterface');
    }
}
