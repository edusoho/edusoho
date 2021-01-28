<?php

namespace Codeages\PluginBundle\Tests\Event;

use Codeages\PluginBundle\Event\LazySubscribers;
use Codeages\PluginBundle\Tests\Event\Fixture\DemoEvent\TestOneEventSubscribers;
use Codeages\PluginBundle\Tests\Event\Fixture\DemoEvent\TestTwoEventSubscribers;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LazySubscribersTest extends TestCase
{
    public function testGetEventMapWithoutService()
    {
        $kernel = $this->mockKernel();

        $container = new ContainerBuilder();
        $container->set('kernel', $kernel);

        $lazySubscribers = $this->getMockBuilder('Codeages\PluginBundle\Event\LazySubscribers')
            ->setMethods(array('getEventMap'))
            ->setConstructorArgs(array($container))
            ->getMockForAbstractClass();
        $lazySubscribers->method('getEventMap')
            ->willReturn(array());
    }

    public function testGetCallbacks()
    {
        $services = array(
            'test_one_event_subscribers' => array(0 => array()),
            'test_two_event_subscribers' => array(0 => array()),
        );
        $kernel = $this->mockKernel();

        $container = new ContainerBuilder();
        $container->set('kernel', $kernel);
        $container->set('test_one_event_subscribers', new TestOneEventSubscribers());
        $container->set('test_two_event_subscribers', new TestTwoEventSubscribers());

        $lazySubscribers = new LazySubscribers($container);

        foreach ($services as $id => $tags) {
            $lazySubscribers->addSubscriberService($id);
        }

        $test1 = array(
            0 => array(
                0 => 'test_two_event_subscribers',
                1 => 'onTest1',
                2 => 0,
            ),
            1 => array(
                0 => 'test_one_event_subscribers',
                1 => 'onTest1',
                2 => 0,
            ),
        );
        $test2 = array(
            0 => array(
                0 => 'test_two_event_subscribers',
                1 => 'onTest2',
                2 => 0,
            ),
            1 => array(
                0 => 'test_one_event_subscribers',
                1 => 'onTest2',
                2 => -100,
            ),
        );
        $test3 = array(
            0 => array(
                0 => 'test_one_event_subscribers',
                1 => 'onTest3',
                2 => 100,
            ),
            1 => array(
                0 => 'test_two_event_subscribers',
                1 => 'onTest3',
                2 => 0,
            ),
        );

        $test1Callbacks = $lazySubscribers->getCallbacks('test1');
        $this->assertEquals($test1, $test1Callbacks);

        $test2Callbacks = $lazySubscribers->getCallbacks('test2');
        $this->assertEquals($test2, $test2Callbacks);

        $test3Callbacks = $lazySubscribers->getCallbacks('test3');
        $this->assertEquals($test3, $test3Callbacks);

        $cacheFileDir = __DIR__.DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'cache';
        unlink($cacheFileDir.DIRECTORY_SEPARATOR.'event_map.php');
        unlink($cacheFileDir.DIRECTORY_SEPARATOR.'event_map.php.meta');
    }

    private function mockKernel()
    {
        $testKernel = $this->getMockBuilder('Codeages\PluginBundle\Tests\Event\Fixture\TestKernel')
            ->setConstructorArgs(array('test', false))
            ->setMethods(array('getCacheDir'))
            ->getMockForAbstractClass();

        $testKernel->method('getCacheDir')
            ->willReturn(__DIR__.DIRECTORY_SEPARATOR.'Fixture'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'cache');

        return $testKernel;
    }
}
