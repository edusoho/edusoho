<?php

namespace Topxia\Service\Common;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Topxia\Service\Common\ServiceKernel;

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

    protected $currentUser = null;

    protected function setCurrentUser ($user)
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);

        $token = new UsernamePasswordToken($currentUser, null, 'main', $currentUser->getRoles());
        static::$kernel->getContainer()->get('security.context')->setToken($token);

        $this->currentUser = $currentUser;
    }

    protected function getCurrentUser()
    {
        return $this->currentUser;
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
    public function setUp()
    {
        if (!static::$isDatabaseCreated) {
            $this->createAppDatabase();
            static::$isDatabaseCreated = true;
        }
        $this->emptyAppDatabase();
        $this->setCurrentUser(array(
            'id' => 1, 
            'email' => 'test@edusoho.com',
            'password' => 'test',
            'roles' => array('ROLE_ADMIN'),
            'currentIp' => '127.0.0.1',
        ));
    }

    protected static function getContainer()
    {
        return static::$kernel->getContainer();
    }

    public function tearDown()
    {
    
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    private  function createAppDatabase()
    {
        // 执行数据库的migrate脚本
        $application = new Application(static::$kernel);
        $application->add(new MigrationsMigrateDoctrineCommand());
        $command = $application->find('doctrine:migrations:migrate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array('command' => $command->getName(), '--no-interaction' => true)
        );
    }

    private function emptyAppDatabase()
    {
        $connection = static::getContainer()->get('database_connection');
        $tableNames = $connection->getSchemaManager()->listTableNames();

        $sql = '';
        foreach ($tableNames as $tableName) {
            if ($tableName == 'migration_versions') {
                continue;
            }
            $sql .= "TRUNCATE {$tableName};";
        }
        $connection->exec($sql);
    }

    protected function assertArrayEquals(Array $ary1,Array $ary2,Array $keyAry=array()){
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