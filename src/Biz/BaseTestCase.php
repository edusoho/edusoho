<?php

namespace Biz;

use AppBundle\Common\ArrayToolkit;
use Biz\Role\Util\PermissionBuilder;
use Biz\TestTool\MockedRequest;
use Biz\UnitTests\DatabaseDataClearer;
use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\PluginBundle\Event\LazyDispatcher;
use CustomBundle\Biz\CustomServiceProvider;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Topxia\Service\Common\ServiceKernel;

class BaseTestCase extends TestCase
{
    /** @var \AppKernel */
    protected static $appKernel;

    /**
     * @var Biz
     */
    protected $biz;

    /** @var \Doctrine\DBAL\Connection */
    protected static $db;

    /** @var \Redis */
    protected static $redis = null;

    public static function setDb($db)
    {
        self::$db = $db;
    }

    public static function setRedis($redis)
    {
        self::$redis = $redis;
    }

    public static function setAppKernel(\AppKernel $appKernel)
    {
        self::$appKernel = $appKernel;
    }

    public function emptyDatabaseQuickly()
    {
        $clear = new DatabaseDataClearer(self::$db);
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

    /**
     * @return CurrentUser
     */
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
        $this->initBiz();
        $this->emptyDatabaseQuickly();
        self::$db->beginTransaction();
        if (self::$redis) {
            self::$redis->flushDb();
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
        //所有的generateUrl 都将mock，单元测试要注意
        $router = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate', 'supports', 'exists', 'getContext'])
            ->getMock();
        $router
            ->expects($this->atLeast(0))
            ->method('getContext')
            ->willReturn(new RequestContext());
        $container->set('router', $router);

        $oldBiz = $container->get('biz');
        $biz = new Biz($container->getParameter('biz_config'));
        self::$appKernel->initializeBiz($biz);
        $biz['db'] = self::$db;
        $biz['redis'] = self::$redis;
        $biz['migration.directories'] = $oldBiz['migration.directories'];
        $biz['autoload.aliases'] = $oldBiz['autoload.aliases'];
        $biz->register(new CustomServiceProvider());

        $this->biz = $biz;
        $biz['dispatcher'] = function () use ($container, $biz) {
            $dispatcher = new TestCaseLazyDispatcher($container);
            $dispatcher->setBiz($biz);

            return $dispatcher;
        };

        return $biz;
    }

    public function tearDown()
    {
        $biz = $this->getBiz();
        $biz['db']->rollback();
        //echo '运行后内存：'.round(memory_get_usage() / 1024 / 1024, 2).'MB', '';
    }

    protected function initDevelopSetting()
    {
        $this->getServiceKernel()->createService('System:SettingService')->set('developer', [
            'without_network' => '1',
        ]);

        return $this;
    }

    protected function initCurrentUser()
    {
        /** @var $userService \Biz\User\Service\UserService */
        $userService = $this->createService('User:UserService');

        $currentUser = new CurrentUser();
        //由于创建管理员用户时，当前用户（CurrentUser）必须有管理员权限，所以在register之前先mock一个临时管理员用户作为CurrentUser
        $currentUser->fromArray([
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'],
            'org' => ['id' => 1, 'orgCode' => '1.'],
        ]);

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $user = $userService->register([
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'createdIp' => '127.0.0.1',
            'orgCode' => '1.',
            'orgId' => '1',
        ]);
        $roles = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'];
        $user = $userService->changeUserRoles($user['id'], $roles);
        $user['currentIp'] = $user['createdIp'];
        $user['org'] = ['id' => 1, 'orgCode' => '1.'];
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
     *          'throwException' => new \Exception(), //object Exception or string Exception ，和returnValue 只能二选一，否则throwException优先
     *          'withParams' => array('param1', array('arrayParamKey1' => '123')),
     *                          //非必填，表示填了相应参数才会有相应返回结果
     *                          //参数必须要用一个数组包含
     *          'runTimes' => 1 //非必填，表示跑第几次会出相应结果, 不填表示无论跑多少此，结果都一样
     *      )
     *  )
     *
     * @return \Mockery\MockInterface
     */
    protected function mockBiz($alias, $params = [])
    {
        $mockedObj = $this->mockObject($alias, $params);

        $biz = $this->getBiz();
        $biz['@'.$alias] = $mockedObj;

        return $mockedObj;
    }

    protected function mockPureBiz($alias, $params = [])
    {
        $mockedObj = $this->mockObject($alias, $params);

        $biz = $this->getBiz();
        $biz[$alias] = $mockedObj;

        return $mockedObj;
    }

    /**
     * @param $config 值为
     *   array(
     *      'request' => array(
     *          'username' => 'aok',
     *          'password' => 'dds',   //$request->request->get('password') 将返回 dss
     *      )
     *   )
     */
    protected function mockRequest($configs)
    {
        return MockedRequest::mockRequest($configs);
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

    /**
     * @param $uniqueCols 如果有值，会以指定的列来认为是数组的唯一键，比较时，只比较 唯一键相同的数组
     *   如 array('id'), 比较的数组 中 id 相等的才会进行比较
     *   注意： 目前只支持设置单属性
     *
     * @deprecated 断言应该写在具体的测试函数内，不应该抽象，这个函数的弊端是，会导致大量的断言出现，抽样就可
     */
    protected function assertArrayEquals(array $arr1, array $arr2, array $keyAry = [], $uniqueCols = [])
    {
        if (count($keyAry) >= 1) {
            foreach ($keyAry as $key) {
                $this->assertEquals($arr1[$key], $arr2[$key]);
            }
        } else {
            if (!empty($uniqueCols)) {
                $formatedArr1 = ArrayToolkit::index($arr1, $uniqueCols[0]);
                $formatedArr2 = ArrayToolkit::index($arr2, $uniqueCols[0]);
            } else {
                $formatedArr1 = $arr1;
                $formatedArr2 = $arr2;
            }

            foreach (array_keys($formatedArr1) as $key) {
                if (is_array($formatedArr1[$key])) {
                    $this->assertArrayEquals($formatedArr1[$key], $formatedArr2[$key]);
                } else {
                    $this->assertEquals($formatedArr1[$key], $formatedArr2[$key]);
                }
            }
        }
    }

    protected function assertArrayValueEquals(array $arr1, array $arr2)
    {
        foreach ($arr1 as $value) {
            $this->assertTrue(in_array($value, $arr2));
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

    protected function mockObject($alias, $params = [])
    {
        $splitedChars = [':', '.'];
        $className = $alias;
        foreach ($splitedChars as $char) {
            $aliasList = explode($char, $className);
            $className = end($aliasList);
        }

        $mockObj = Mockery::mock($className);

        foreach ($params as $param) {
            $expectation = $mockObj->shouldReceive($param['functionName']);

            if (isset($param['runTimes'])) {
                $expectation = $expectation->times($param['runTimes']);
            }

            if (!empty($param['withParams'])) {
                $expectation = $expectation->withArgs($param['withParams']);
            } else {
                $expectation = $expectation->withAnyArgs();
            }

            if (isset($param['returnValue'])) {
                $expectation->andReturn($param['returnValue']);
            }

            if (isset($param['andReturnValues'])) {
                $expectation->andReturnValues($param['andReturnValues']);
            }

            if (isset($param['throwException'])) {
                $expectation->andThrow($param['throwException']);
            }
        }

        return $mockObj;
    }
}

class TestCaseLazyDispatcher extends LazyDispatcher
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Biz
     */
    private $biz;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->container = $container;
    }

    public function dispatch($eventName, Event $event = null)
    {
        if (null === $event) {
            $event = new Event();
        }

//        $event->setDispatcher($this);
//        $event->setName($eventName);

        $subscribers = $this->container->get('codeags_plugin.event.lazy_subscribers');

        $callbacks = $subscribers->getCallbacks($eventName);

        foreach ($callbacks as $callback) {
            if ($event->isPropagationStopped()) {
                break;
            }

            list($id, $method) = $callback;
            if ($this->container->has($id)) {
                $subscriber = $this->container->get($id);
                $className = get_class($subscriber);
                $newSubscriber = new $className($this->biz);
                call_user_func([$newSubscriber, $method], $event);
            }
        }

        return $event;
    }

    public function setBiz($biz)
    {
        $this->biz = $biz;
    }
}
