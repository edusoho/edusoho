<?php

namespace Topxia\Service\Common;

use Mockery;
use Topxia\Service\User\CurrentUser;
use Permission\Common\PermissionBuilder;

class BaseTestCase extends \Codeages\Biz\Framework\UnitTests\BaseTestCase
{
    protected function getCurrentUser()
    {
        return $this->getServiceKernel()->getCurrentUser();
    }

    public function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $_SERVER['HTTP_HOST'] = 'test.com'; //mock $_SERVER['HTTP_HOST'] for http request testing
    }

    public function setUp()
    {
        parent::emptyDatabase();
        $this->initServiceKernel()
            ->flushPool()
            ->initDevelopSetting()
            ->initCurrentUser();
    }

    protected function initDevelopSetting()
    {
        $this->getServiceKernel()->createService('System.SettingService')->set('developer', array(
            'without_network' => '1'
        ));

        return $this;
    }

    protected function initServiceKernel()
    {
        $serviceKernel = $this->getServiceKernel();
        $serviceKernel->setBiz(self::$biz);
        return $this;
    }

    protected function initCurrentUser()
    {
        $userService = $this->getServiceKernel()->createService('User.UserService');

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id'        => 0,
            'nickname'  => '游客',
            'currentIp' => '127.0.0.1',
            'roles'     => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org'       => array('id' => 1)
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser);

        $user = $userService->register(array(
            'nickname'  => 'admin',
            'email'     => 'admin@admin.com',
            'password'  => 'admin',
            'createdIp' => '127.0.0.1',
            'orgCode'   => '1.',
            'orgId'     => '1'
        ));
        $roles             = array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');
        $user              = $userService->changeUserRoles($user['id'], $roles);
        $user['currentIp'] = $user['createdIp'];
        $user['org']       = array('id' => 1);
        $currentUser       = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->getServiceKernel()->createService('Permission:Role.RoleService')->refreshRoles();
        $this->getServiceKernel()->getCurrentUser()->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));

        return $this;
    }

    /**
     * mock对象
     *
     * @param string $objectName mock的类名
     * @param array  $params     mock对象时的参数,array,包含 $functionName,$withParams,$runTimes和$returnValue
     */

    protected function mock($objectName, $params = array())
    {
        $newService = explode('.', $objectName);
        $mockObject = Mockery::mock($newService[1]);

        foreach ($params as $key => $param) {
            $mockObject->shouldReceive($param['functionName'])->times($param['runTimes'])->withAnyArgs()->andReturn($param['returnValue']);
        }

        $pool              = array();
        $pool[$objectName] = $mockObject;
        $this->setPool($pool);
    }

    protected function setPool($object)
    {
        $reflectionObject = new \ReflectionObject($this->getServiceKernel());
        $pool             = $reflectionObject->getProperty("pool");
        $pool->setAccessible(true);
        $value   = $pool->getValue($this->getServiceKernel());
        $objects = array_merge($value, $object);
        $pool->setValue($this->getServiceKernel(), $objects);
    }

    protected function flushPool()
    {
        $reflectionObject = new \ReflectionObject($this->getServiceKernel());
        $pool             = $reflectionObject->getProperty("pool");
        $pool->setAccessible(true);
        $pool->setValue($this->getServiceKernel(), array());

        return $this;
    }

    protected static function getContainer()
    {
        global $kernel;
        return $kernel->getContainer();
    }

    protected function assertArrayEquals(array $ary1, array $ary2, array $keyAry = array())
    {
        if (count($keyAry) >= 1) {
            foreach ($keyAry as $key) {
                $this->assertEquals($ary1[$key], $ary2[$key]);
            }
        } else {
            foreach ($ary1 as $key => $value) {
                $this->assertEquals($ary1[$key], $ary2[$key]);
            }
        }
    }
}
