<?php

namespace Topxia\Service\Common;

use Mockery;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Doctrine\Bundle\MigrationsBundle\Command\MigrationsMigrateDoctrineCommand;

class BaseTestCase extends WebTestCase
{
    protected static $isDatabaseCreated = false;

    protected static $serviceKernel = null;

    protected function getCurrentUser()
    {
        return static::$serviceKernel->getCurrentUser();
    }

    public static function setUpBeforeClass()
    {
        $_SERVER['HTTP_HOST'] = 'test.com'; //mock $_SERVER['HTTP_HOST'] for http request testing
        static::$kernel       = static::createKernel();
        static::$kernel->boot();
    }

    protected function setServiceKernel()
    {
        if (static::$serviceKernel) {
            return;
        }

        $kernel = new \AppKernel('test', false);
        $kernel->loadClassCache();
        $kernel->boot();
        Request::enableHttpMethodParameterOverride();
        $request = Request::createFromGlobals();

        $serviceKernel = ServiceKernel::create($kernel->getEnvironment(), $kernel->isDebug());
        $serviceKernel->setParameterBag($kernel->getContainer()->getParameterBag());
        $connection = $kernel->getContainer()->get('database_connection');
        $serviceKernel->setConnection(new TestCaseConnection($connection));
        $serviceKernel->setEnvVariable(array(
            'host'          => 'test.com',
            'schemeAndHost' => 'http://test.com'
        ));
        static::$serviceKernel = $serviceKernel;
    }

    public function getServiceKernel()
    {
        return static::$serviceKernel;
    }

    /**
     * 每个testXXX执行之前，都会执行此函数，净化数据库。
     *
     * NOTE: 如果数据库已创建，那么执行清表操作，不重建。
     */
    public function setUp()
    {
        $this->setServiceKernel();

        $this->flushPool();

        if (!static::$isDatabaseCreated) {
            $this->createAppDatabase();
            static::$isDatabaseCreated = true;
            $this->emptyAppDatabase(true);
        } else {
            $this->emptyAppDatabase(false);
        }

        $this->initCurrentUser();
        $this->initDevelopSetting();
    }

    protected function initDevelopSetting()
    {
        static::$serviceKernel->createService('System.SettingService')->set('developer', array(
            'without_network' => '1'
        ));
    }

    protected function initCurrentUser()
    {
        $userService = static::$serviceKernel->createService('User.UserService');

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id'        => 0,
            'nickname'  => '游客',
            'currentIp' => '127.0.0.1',
            'roles'     => array(),
            'org'       => array('id' => 1)
        ));
        static::$serviceKernel->setCurrentUser($currentUser);

        $user  = $userService->register(array(
            'nickname'  => 'admin',
            'email'     => 'admin@admin.com',
            'password'  => 'admin',
            'createdIp' => '127.0.0.1',
            'orgCode'   => '1.',
            'orgId'     => '1'
        ));
        $roles = array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');
        $userService->changeUserRoles($user['id'], $roles);
        $user              = $userService->getUserByEmail($user['email']);
        $user['currentIp'] = $user['createdIp'];
        $user['org']       = array('id' => 1);
        $currentUser       = new CurrentUser();
        $currentUser->fromArray($user);
        static::$serviceKernel->setCurrentUser($currentUser);
    }

    /**
     * mock对象
     *
     * @param $name                                       mock的类名
     * @param $params                                     ,mock对象时的参数,array,包含 $functionName,$withParams,$runTimes和$returnValue
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
        $reflectionObject = new \ReflectionObject(static::$serviceKernel);
        $pool             = $reflectionObject->getProperty("pool");
        $pool->setAccessible(true);
        $value   = $pool->getValue(static::$serviceKernel);
        $objects = array_merge($value, $object);
        $pool->setValue(static::$serviceKernel, $objects);
    }

    protected function flushPool()
    {
        $reflectionObject = new \ReflectionObject(static::$serviceKernel);
        $pool             = $reflectionObject->getProperty("pool");
        $pool->setAccessible(true);
        $pool->setValue(static::$serviceKernel, array());
    }

    protected static function getContainer()
    {
        return static::$kernel->getContainer();
    }

    public function tearDown()
    {
    }

    protected function createAppDatabase()
    {
        // 执行数据库的migrate脚本
        $application = new Application(static::$kernel);
        $application->add(new MigrationsMigrateDoctrineCommand());
        $command       = $application->find('doctrine:migrations:migrate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array('command' => $command->getName()),
            array('interactive' => false)
        );
    }

    protected function emptyAppDatabase($emptyAll = true)
    {
        $connection = static::$serviceKernel->getConnection();

        if ($emptyAll) {
            $tableNames = $connection->getSchemaManager()->listTableNames();
        } else {
            $tableNames = $connection->getInsertedTables();
            $tableNames = array_unique($tableNames);
        }

        $tableWhiteList = array(
            'migration_versions',
            'file_group'
        );

        $sql = '';

        foreach ($tableNames as $tableName) {
            if (in_array($tableName, $tableWhiteList)) {
                continue;
            }

            $sql .= "TRUNCATE {$tableName};";
        }

        if (!empty($sql)) {
            $connection->exec($sql);
            $connection->resetInsertedTables();
        }
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
