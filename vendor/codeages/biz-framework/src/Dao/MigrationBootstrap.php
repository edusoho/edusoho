<?php

namespace Codeages\Biz\Framework\Dao;

use Phpmig\Adapter;
use Pimple\Container;

class MigrationBootstrap
{
    protected $db;

    protected $directories;

    public function __construct($db, \ArrayObject $directories)
    {
        $this->db = $db;
        $this->directories = $directories;
    }

    public function boot()
    {
        $container = new Container();
        $container['db'] = $this->db;

        // see: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/mysql-enums.html
        $container['db']->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        $container['phpmig.adapter'] = function ($container) {
            return new Adapter\Doctrine\DBAL($container['db'], 'migrations');
        };

        $migrations = array();
        foreach ($this->directories as $directory) {
            $migrations = array_merge($migrations, glob("{$directory}/*.php"));
        }
        $container['phpmig.migrations'] = $migrations;

        if (count($this->directories) > 0) {
            $container['phpmig.migrations_path'] = reset($this->directories);
        }

        return $container;
    }
}
