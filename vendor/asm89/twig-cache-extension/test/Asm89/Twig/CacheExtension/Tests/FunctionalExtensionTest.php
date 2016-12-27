<?php

/*
 * This file is part of twig-cache-extension.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\CacheExtension\Tests;

use Asm89\Twig\CacheExtension\CacheProvider\DoctrineCacheAdapter;
use Asm89\Twig\CacheExtension\CacheStrategy\KeyGeneratorInterface;
use Asm89\Twig\CacheExtension\CacheStrategy\GenerationalCacheStrategy;
use Asm89\Twig\CacheExtension\CacheStrategy\IndexedChainingCacheStrategy;
use Asm89\Twig\CacheExtension\CacheStrategy\LifetimeCacheStrategy;
use Asm89\Twig\CacheExtension\Extension;
use Doctrine\Common\Cache\ArrayCache;
use Twig_Loader_Filesystem;
use Twig_Environment;

class FunctionalExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $cache;

    protected function createCacheProvider()
    {
        $this->cache = new ArrayCache();

        return new DoctrineCacheAdapter($this->cache);
    }

    protected function createCacheStrategy($name = null)
    {
        $cacheProvider = $this->createCacheProvider();
        $keyGenerator  = $this->createKeyGenerator();
        $lifetime      = 0;

        switch ($name) {
            case 'time':
                return new LifetimeCacheStrategy($cacheProvider);
            case 'indexed':
                return new IndexedChainingCacheStrategy(array(
                    'gcs'  => new GenerationalCacheStrategy($cacheProvider, $keyGenerator, $lifetime),
                    'time' => new LifetimeCacheStrategy($cacheProvider),
                ));
            default:
                return new GenerationalCacheStrategy($cacheProvider, $keyGenerator, $lifetime);
        }
    }

    protected function createKeyGenerator()
    {
        return new KeyGenerator();
    }

    protected function createTwig($cacheStrategyName = null)
    {
        $loader = new Twig_Loader_Filesystem(__DIR__ . '/fixtures/');
        $twig = new Twig_Environment($loader);

        $cacheExtension = new Extension($this->createCacheStrategy($cacheStrategyName));

        $twig->addExtension($cacheExtension);

        return $twig;
    }

    protected function getValue($value, $updatedAt)
    {
        return new Value($value, $updatedAt);
    }

    public function testCachesWithSameCacheKey()
    {
        $twig = $this->createTwig();

        $rendered = $twig->render('gcs_value.twig', array('value' => $this->getValue('asm89', 1)));
        $this->assertEquals('Hello asm89!', $rendered);

        $rendered2 = $twig->render('gcs_value.twig', array('value' => $this->getValue('fabpot', 1)));
        $this->assertEquals('Hello asm89!', $rendered2);
    }

    public function testDifferentCacheOnDifferentAnnotation()
    {
        $twig = $this->createTwig();

        $rendered = $twig->render('gcs_value.twig', array('value' => $this->getValue('asm89', 1)));
        $this->assertEquals('Hello asm89!', $rendered);

        $rendered2 = $twig->render('gcs_value.twig', array('value' => $this->getValue('fabpot', 1)));
        $this->assertEquals('Hello asm89!', $rendered2);

        $rendered3 = $twig->render('gcs_value_v2.twig', array('value' => $this->getValue('fabpot', 1)));
        $this->assertEquals('Hello fabpot!', $rendered3);
    }

    public function testLifetimeCacheStrategy()
    {
        $twig = $this->createTwig('time');

        $rendered = $twig->render('lcs_value.twig', array('value' => $this->getValue('asm89', 1)));
        $this->assertEquals('Hello asm89!', $rendered);

        $rendered2 = $twig->render('lcs_value.twig', array('value' => $this->getValue('fabpot', 1)));
        $this->assertEquals('Hello asm89!', $rendered2);

        $this->cache->flushAll();

        $rendered3 = $twig->render('lcs_value.twig', array('value' => $this->getValue('fabpot', 1)));
        $this->assertEquals('Hello fabpot!', $rendered3);
    }

    public function testIndexedChainingStrategy()
    {
        $twig = $this->createTwig('indexed');

        $rendered = $twig->render('ics_value.twig', array('value' => $this->getValue('asm89', 1)));
        $this->assertEquals('Hello asm89!', $rendered);

        $rendered2 = $twig->render('ics_value.twig', array('value' => $this->getValue('fabpot', 1)));
        $this->assertEquals('Hello asm89!', $rendered2);

        $this->cache->flushAll();

        $rendered3 = $twig->render('ics_value.twig', array('value' => $this->getValue('fabpot', 1)));
        $this->assertEquals('Hello fabpot!', $rendered3);
    }

    /**
     * @expectedException Twig_Error_Runtime
     * @expectedExceptionMessage An exception has been thrown during the rendering of a template ("No strategy key found in value.") in "ics_no_key.twig" at line 1.
     */
    public function testIndexedChainingStrategyNeedsKey()
    {
        $twig = $this->createTwig('indexed');

        $twig->render('ics_no_key.twig', array('value' => $this->getValue('asm89', 1)));
    }

    public function testAnnotationExpression()
    {
        $twig = $this->createTwig('indexed');

        $rendered = $twig->render('annotation_expression.twig', array('value' => $this->getValue('asm89', 1), 'version' => 1));
        $this->assertEquals('Hello asm89!Hello asm89!', $rendered);
    }
}

class KeyGenerator implements KeyGeneratorInterface
{
    public function generateKey($value)
    {
        return get_class($value) . '_' . $value->getUpdatedAt();
    }

}

class Value
{
    private $value;
    private $updatedAt;

    public function __construct($value, $updatedAt)
    {
        $this->value     = $value;
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function __toString()
    {
        return $this->value;
    }
}
