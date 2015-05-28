<?php

namespace Topxia\Service\Common;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Topxia\Service\User\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Doctrine\Bundle\MigrationsBundle\Command\MigrationsMigrateDoctrineCommand;

class BaseTestCase extends WebTestCase
{
    protected static $isDatabaseCreated = false;

    protected static $serviceKernel = null;

    protected function getCurrentUser()
    {
        return static::$serviceKernel->getCurrentUser();;
    }


    public static function setUpBeforeClass()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
    }

    /**
     * 每个testXXX执行之前，都会执行此函数，净化数据库。
     * 
     * NOTE: 如果数据库已创建，那么执行清表操作，不重建。
     */

    private function setServiceKernel()
    {
        if (static::$serviceKernel) {
            return ;
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
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 1,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password'=>'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER','ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER')
        ));
        $serviceKernel->setCurrentUser($currentUser);

        static::$serviceKernel = $serviceKernel;
    }

    public function getServiceKernel()
    {
        return static::$serviceKernel;
    }

    public function setUp()
    {
        $this->setServiceKernel();

        if (!static::$isDatabaseCreated) {
            $this->createAppDatabase();
            static::$isDatabaseCreated = true;
            $this->emptyAppDatabase(true);
        } else {
            $this->emptyAppDatabase(false);
        }
        

        static::$serviceKernel->createService('User.UserService')->register(array(
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password'=>'admin',
            'loginIp' => '127.0.0.1',
            'roles' => array('ROLE_USER','ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER')
        ));
    }

    protected static function getContainer()
    {
        return static::$kernel->getContainer();
    }

    public function tearDown()
    {
    
    }

    private  function createAppDatabase()
    {
        // 执行数据库的migrate脚本
        $application = new Application(static::$kernel);
        $application->add(new MigrationsMigrateDoctrineCommand());
        $command = $application->find('doctrine:migrations:migrate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
        array('command' => $command->getName()),
        array('interactive' => false)
        );
    }

    private function emptyAppDatabase($emptyAll = true)
    {
        $connection = static::$serviceKernel->getConnection();

        if ($emptyAll) {
            $tableNames = $connection->getSchemaManager()->listTableNames();
        } else {
            $tableNames = $connection->getInsertedTables();
            $tableNames = array_unique($tableNames);
        }

        $sql = '';
        foreach ($tableNames as $tableName) {
            if ($tableName == 'migration_versions') {
                continue;
            }
            $sql .= "TRUNCATE {$tableName};";
        }
        if (!empty($sql)) {
            $connection->exec($sql);
            $connection->resetInsertedTables();
        }

    }

    protected function assertArrayEquals(Array $ary1,Array $ary2,Array $keyAry=array())
    {
        if(count($keyAry)>=1){
            foreach ($keyAry as $key){
                $this->assertEquals($ary1[$key],$ary2[$key]);
            }
        }else{
            foreach ($ary1 as $key=>$value){
                $this->assertEquals($ary1[$key],$ary2[$key]);
            }
        }
    }

}