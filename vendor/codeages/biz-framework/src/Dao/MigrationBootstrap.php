<?php

namespace Codeages\Biz\Framework\Dao;

use Phpmig\Adapter;
use Pimple\Container;

class MigrationBootstrap
{
    protected $db;

    protected $directories;

    protected $table;

    public function __construct($db, \ArrayObject $directories, $table = 'migrations')
    {
        $this->db = $db;
        $this->directories = $directories;
        $this->table = $table;
    }

    public function boot()
    {
        $container = new Container();
        $container['db'] = $this->db;

        see: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/mysql-enums.html
        $container['db']->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        $table = $this->table;
        $container['phpmig.adapter'] = function ($container) use ($table) {
            return new Adapter\Doctrine\DBAL($container['db'], $table);
        };

        $migrations = array();
        foreach ($this->directories as $directory) {
            $migrations = array_merge($migrations, glob("{$directory}/*.php"));
        }
        $container['phpmig.migrations'] = $migrations;

        if (count($this->directories) > 0) {
            $i = $this->directories->getIterator();
            $i->rewind();
            $container['phpmig.migrations_path'] = $i->current();
        }

        return $container;
    }
}
