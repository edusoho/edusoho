<?php

namespace Biz;

use Mockery;
use Biz\User\CurrentUser;
use Biz\Role\Util\PermissionBuilder;
use Codeages\Biz\Framework\Context\Biz;
use Topxia\Service\Common\ServiceKernel;

class BaseTestCase extends \Codeages\Biz\Framework\UnitTests\BaseTestCase
{
    protected function createService($alias)
    {
        return $this->getBiz()->service($alias);
    }

    protected function createDao($alias)
    {
        return $this->getBiz()->Dao($alias);
    }

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
        $_SERVER['HTTP_HOST'] = 'test.com'; //mock $_SERVER['HTTP_HOST'] for http request testing
    }

    public function setUp()
    {
        $biz = $this->getBiz();
        parent::emptyDatabaseQuickly();
        $biz['db']->beginTransaction();
        if (isset($biz['redis'])) {
            $biz['redis']->flushDb();
            //$biz['dao.cache.shared_storage']->flush();
        }
        $this
            ->flushPool()
            ->initDevelopSetting()
            ->initCurrentUser();
    }

    public function tearDown()
    {
        $biz = $this->getBiz();
        $keys = $biz->keys();

        foreach ($keys as $key) {
            if (substr($key, 0, 1) === '@' && substr($key, 0, 8) != '@Custom:') {
                unset($biz[$key]);
            }
        }

        $biz['db']->rollback();
    }

    protected function initDevelopSetting()
    {
        $this->getServiceKernel()->createService('System:SettingService')->set('developer', array(
            'without_network' => '1',
        ));

        return $this;
    }

    protected function initCurrentUser()
    {
        $userService = ServiceKernel::instance()->createService('User:UserService');

        $currentUser = new CurrentUser();
        //由于创建管理员用户时，当前用户（CurrentUser）必须有管理员权限，所以在register之前先mock一个临时管理员用户作为CurrentUser
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1),
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser);

        $user = $userService->register(array(
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'createdIp' => '127.0.0.1',
            'orgCode' => '1.',
            'orgId' => '1',
        ));
        $roles = array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');
        $user = $userService->changeUserRoles($user['id'], $roles);
        $user['currentIp'] = $user['createdIp'];
        $user['org'] = array('id' => 1);
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->grantPermissionToUser($currentUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->getServiceKernel()->createService('Role:RoleService')->refreshRoles();
        $this->getServiceKernel()->getCurrentUser()->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));

        $biz = $this->getBiz();
        $biz['user'] = $this->getCurrentUser();

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

        foreach ($params as $param) {
            $mockObject->shouldReceive($param['functionName'])->times($param['runTimes'])->withAnyArgs()->andReturn($param['returnValue']);
        }

        $pool = array();
        $pool[$objectName] = $mockObject;
        $this->setPool($pool);
    }

    /**
     * 用于 mock　service　和　dao
     * 如　$this->mockBiz(
     *      'Course:CourseService',
     *       array(
     *          array(
     *              'functionName' => 'tryManageCourse',
     *              'returnValue' => array('id' => 1),
     *          ),
     *      )
     *  );
     * ＠param $alias  createService　或　createDao 里面的字符串
     * ＠param $params 二维数组
     *  array(
     *      array(
     *          'functionName' => 'tryManageCourse',　//必填
     *          'returnValue' => array('id' => 1),　// 非必填，填了表示有相应的返回结果
     *          'withParams' => array('param1', array('arrayParamKey1' => '123')),　
     *                          //非必填，表示填了相应参数才会有相应返回结果
     *                          //参数必须要用一个数组包含
     *          'runTimes' => 1 //非必填，表示跑第几次会出相应结果, 不填表示无论跑多少此，结果都一样
     *      )
     *  )
     */
    protected function mockBiz($alias, $params = array())
    {
        $aliasList = explode(':', $alias);
        $className = end($aliasList);
        $mockObj = Mockery::mock($className);

        foreach ($params as $param) {
            $expectation = $mockObj->shouldReceive($param['functionName']);

            if (!empty($param['runTimes'])) {
                $expectation = $expectation->times($param['runTimes']);
            }

            if (!empty($param['withParams'])) {
                $expectation = $expectation->withArgs($param['withParams']);
            } else {
                $expectation = $expectation->withAnyArgs();
            }

            if (!empty($param['returnValue'])) {
                $expectation->andReturn($param['returnValue']);
            }
        }

        $biz = $this->getBiz();
        $biz['@'.$alias] = $mockObj;
    }

    protected function setPool($object)
    {
        $reflectionObject = new \ReflectionObject($this->getServiceKernel());
        $pool = $reflectionObject->getProperty('pool');
        $pool->setAccessible(true);
        $value = $pool->getValue($this->getServiceKernel());
        $objects = array_merge($value, $object);
        $pool->setValue($this->getServiceKernel(), $objects);
    }

    protected function flushPool()
    {
        $reflectionObject = new \ReflectionObject($this->getServiceKernel());
        $pool = $reflectionObject->getProperty('pool');
        $pool->setAccessible(true);
        $pool->setValue($this->getServiceKernel(), array());

        return $this;
    }

    protected static function getContainer()
    {
        global $kernel;

        return $kernel->getContainer();
    }

    /**
     * @return Biz
     */
    protected function getBiz()
    {
        return self::$biz;
    }

    protected function assertArrayEquals(array $ary1, array $ary2, array $keyAry = array())
    {
        if (count($keyAry) >= 1) {
            foreach ($keyAry as $key) {
                $this->assertEquals($ary1[$key], $ary2[$key]);
            }
        } else {
            foreach (array_keys($ary1) as $key) {
                $this->assertEquals($ary1[$key], $ary2[$key]);
            }
        }
    }

    protected function assertArraySternEquals(array $ary1, array $ary2)
    {
        foreach ($ary1 as $key => $item) {
            $this->assertEquals($item, $ary2[$key]);
        }
    }

    protected function grantPermissionToUser($currentUser)
    {
        $permissions = new \ArrayObject();
        $permissions['admin_course_content_manage'] = true;
        $currentUser->setPermissions($permissions);
    }
}
