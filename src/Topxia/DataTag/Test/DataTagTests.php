<?php
use PHPUnit_Framework_TestCase;
use Topxia\DataTag\HotGroupDataTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;

class DataTagTests extends PHPUnit_Framework_TestCase
{
    public function testHotGroupDataTag()
    {
        $Group = new HotGroupDataTag(1);

        $groups = $Group->getData(array('count'=>5));
    
        $this->assertEquals(5, count($groups));
    }


    public function __construct()
    {
        $loader = require_once __DIR__.'/../../../../app/bootstrap.php.cache';
        Debug::enable();

        require_once __DIR__.'/../../../../app/AppKernel.php';

        $kernel = new AppKernel('dev', true);
        $kernel->loadClassCache();
        Request::enableHttpMethodParameterOverride();
        $request = Request::createFromGlobals();

        $kernel->boot();

        // START: init service kernel
        $serviceKernel = ServiceKernel::create($kernel->getEnvironment(), $kernel->isDebug());
        $serviceKernel->setEnvVariable(array(
            'host' => $request->getHttpHost(),
            'schemeAndHost' => $request->getSchemeAndHttpHost(),
            'basePath' => $request->getBasePath(),
            'baseUrl' =>  $request->getSchemeAndHttpHost() . $request->getBasePath(),
        ));

        $serviceKernel->setParameterBag($kernel->getContainer()->getParameterBag());
        $serviceKernel->setConnection($kernel->getContainer()->get('database_connection'));
        $serviceKernel->getConnection()->exec('SET NAMES UTF8');

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' =>  $request->getClientIp(),
            'roles' => array(),
        ));
        $serviceKernel->setCurrentUser($currentUser);
    }

}