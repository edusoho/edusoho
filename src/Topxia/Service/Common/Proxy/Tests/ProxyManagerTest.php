<?php

namespace Topxia\Service\Common\Proxy\Test;

use Topxia\Service\Common\Proxy\ProxyManager;
use Topxia\Service\Common\BaseTestCase;

class ProxyManagerTest extends BaseTestCase
{
    public function testCreate()
    {
        $class = 'Topxia\\Service\\Common\\Proxy\\Tests\\TestClass';
        $obj = ProxyManager::create($class);
        $this->assertEquals(get_class($obj), 'Topxia\Service\Common\Proxy\ProxyFramework');
    }

    public function testBeforeAnnotation()
    {
        $class = 'Topxia\\Service\\Common\\Proxy\\Tests\\TestClass';
        $obj = ProxyManager::create($class);
        ob_start();
        $obj->before();
        $result = ob_get_clean();
        $this->assertEquals($result, 'run annotation!run before method!');
    }

    public function testAfterAnnotation()
    {
        $class = 'Topxia\\Service\\Common\\Proxy\\Tests\\TestClass';
        $obj = ProxyManager::create($class);
        ob_start();
        $obj->after();
        $result = ob_get_clean();
        $this->assertEquals($result, 'run after method!run annotation!');
    }

    public function testAroundAnnotation()
    {
        $class = 'Topxia\\Service\\Common\\Proxy\\Tests\\TestClass';
        $obj = ProxyManager::create($class);
        ob_start();
        $obj->around();
        $result = ob_get_clean();
        $this->assertEquals($result, 'before run method!run around method!after run method!');
    }
}
