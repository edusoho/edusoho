<?php

namespace Biz;

use Codeages\Biz\Framework\UnitTests\DatabaseDataClearer;
use Mockery;
use Biz\User\CurrentUser;
use Biz\Role\Util\PermissionBuilder;
use Codeages\Biz\Framework\Context\Biz;
use Topxia\Service\Common\ServiceKernel;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /** @var $appKernel \AppKernel  */
    protected static $appKernel;

    protected $biz;

    public static function setAppKernel(\AppKernel $appKernel)
    {
        self::$appKernel = $appKernel;
    }

    public static function db()
    {
        $singletonBiz = self::$appKernel->getContainer()->get('biz');
        return $singletonBiz['db'];
    }

    public function emptyDatabaseQuickly()
    {
        $clear = new DatabaseDataClearer(self::db());
        $clear->clearQuickly();
    }

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
        return $this->biz['user'];
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
        $biz = $this->initBiz();
        $this->emptyDatabaseQuickly();
        self::db()->beginTransaction();
        if (isset($biz['redis'])) {
            $biz['redis']->flushDb();
        }

        $this
            ->initDevelopSetting()
            ->initCurrentUser();
    }

    /**
     * @return Biz
     */
    protected function initBiz()
    {
        $container = self::$appKernel->getContainer();
        $biz = new Biz($container->getParameter('biz_config'));
        self::$appKernel->initializeBiz($biz);
        $singletonBiz = $container->get('biz');
        $biz['dispatcher'] = $singletonBiz['dispatcher'];
        $biz['db'] = $singletonBiz['db'];
        $biz['redis'] = $singletonBiz['redis'];

        $this->biz = $biz;
        return $biz;
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
        /** @var $userService \Biz\User\Service\UserService */
        $userService = $this->createService('User:UserService');

        $currentUser = new CurrentUser();
        //由于创建管理员用户时，当前用户（CurrentUser）必须有管理员权限，所以在register之前先mock一个临时管理员用户作为CurrentUser
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1),
        ));

        $this->getServiceKernel()->setBiz($this->getBiz());
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
        $biz['user'] = $currentUser;

        $container = self::$appKernel->getContainer();
        $singletonBiz = $container->get('biz');
        $singletonBiz['user'] = $currentUser;

        return $this;
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
        return $this->biz;
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
        /* @var $currentUser CurrentUser */
        $currentUser->setPermissions($permissions);
    }
}
