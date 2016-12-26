<?php

use Codeages\Biz\Framework\Context\Biz;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Codeages\Biz\Framework\Provider\MonologServiceProvider;

class BizTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $biz = new Biz();
        $this->assertInstanceOf('Codeages\Biz\Framework\Context\Biz', $biz);

        $config = array(
            'debug' => true,
            'migration.directories' => array('migrations'),
        );
        $biz = new Biz($config);
        $this->assertEquals($config['debug'], $biz['debug']);
        $this->assertEquals($config['migration.directories'], $biz['migration.directories']);
    }

    public function testRegister()
    {
        $biz = new Biz();
        $biz->register(new BizTestServiceProvider1(), array(
            'test_1.options' => array(
                'option1' => 'option1_value',
                'option2' => 'option2',
            ),
        ));

        $this->assertEquals('test_1', $biz['test_1']);
        $this->assertEquals('option1_value', $biz['test_1.options']['option1']);

        $biz->register(new MonologServiceProvider());
    }

    public function testBoot()
    {
        $biz = new Biz();
        $biz->boot();
        $biz->boot();
    }

    public function testRegisterAutoloadAlias()
    {
        $biz = new Biz();
        $biz['autoload.aliases'][''] = 'Biz';
        $biz['autoload.aliases']['TestProject'] = 'TestProject\Biz';
        $this->assertEquals(2, count($biz['autoload.aliases']));
    }

    public function testService()
    {
        $biz = new Biz();
        $biz['autoload.aliases']['TestProject'] = 'TestProject\Biz';
        $service = $biz->service('TestProject:Example:ExampleService');
        $this->assertInstanceOf('TestProject\Biz\Example\Service\ExampleService', $service);
        $this->assertEquals($service, $biz['@TestProject:Example:ExampleService']);

        $biz = new Biz();
        $biz['autoload.aliases']['TestProject'] = 'TestProject\Biz';
        $service1 = $biz->service('TestProject:Example:ExampleService');
        $service2 = $biz->service('TestProject:Example:ExampleService');
        $this->assertEquals($service1, $service2);
    }

    public function testDao()
    {
        $biz = new Biz();
        $biz['autoload.aliases']['TestProject'] = 'TestProject\Biz';
        $dao = $biz->dao('TestProject:Example:ExampleDao');
        $this->assertEquals($dao, $biz['@TestProject:Example:ExampleDao']);

        $biz = new Biz();
        $biz['autoload.aliases']['TestProject'] = 'TestProject\Biz';
        $dao1 = $biz->dao('TestProject:Example:ExampleDao');
        $dao2 = $biz->dao('TestProject:Example:ExampleDao');
        $this->assertEquals($dao1, $dao2);
    }
    
}

class BizTestServiceProvider1 implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['test_1'] = 'test_1';
    }
}
