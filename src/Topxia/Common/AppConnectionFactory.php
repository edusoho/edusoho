<?php
namespace Topxia\Common;

use Topxia\Service\Common\ConnectionFactory;
use Topxia\Service\Common\ServiceKernel;

class AppConnectionFactory implements ConnectionFactory
{
    protected $container;
    protected $connection;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getConnection()
    {
        if(empty($this->connection)){
            $connection = $this->container->get('database_connection');
            // $connection = $this->createConnection();
            // $connection->setServerHost('127.0.0.1');
            // $connection->setServerPort('9501');
            
            $connection->exec('SET NAMES UTF8');
            $this->connection = $connection;
        }

        return $this->connection;
    }
}