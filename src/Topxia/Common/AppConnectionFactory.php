<?php
namespace Topxia\Common;

use Topxia\Service\Common\ConnectionFactory;

class AppConnectionFactory implements ConnectionFactory
{

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getConnection()
    {
        $connection = $this->container->get('database_connection');
        $connection->exec('SET NAMES UTF8');
        return $connection;
    }

}