<?php
namespace Topxia\Common;

use Topxia\Service\Common\ConnectionFactory;
use Topxia\Service\Common\ServiceKernel;
use Doctrine\DBAL\DriverManager;

class AppConnectionFactory implements ConnectionFactory
{
    protected $connection;

    public function getConnection()
    {
        if(empty($this->connection)){

            $connection = DriverManager::getConnection(array(
                'wrapperClass' => 'Codeages\\Biz\\Framework\\Dao\\Connection',
                'driver'       => ServiceKernel::instance()->getParameter('database_driver'),
                'charset'      => 'utf8',
                'host' => ServiceKernel::instance()->getParameter('database_host'),
                'port' => ServiceKernel::instance()->getParameter('database_port'),
                'dbname' => ServiceKernel::instance()->getParameter('database_name'),
                'user' => ServiceKernel::instance()->getParameter('database_user'),
                'password' => ServiceKernel::instance()->getParameter('database_password'),
            ));

            $connection->exec('SET NAMES UTF8');
            $this->connection = $connection;
        }

        return $this->connection;
    }
}
