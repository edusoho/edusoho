<?php

namespace Codeages\Biz\Framework\Testing;

use Codeception\Module as CodeceptionModule;
use Codeception\Configuration;
use Codeages\Biz\Framework\Utility\Env;
use Codeages\Biz\Framework\Context\Biz;
use Codeception\Exception\ModuleRequireException;

class BizCodeceptionModule extends CodeceptionModule implements \Codeception\Lib\Interfaces\Db
{
    protected $bizConfig;

    /**
     * @var Biz
     */
    protected $biz;

    public $config = [
        'class' => '\Biz\AppBiz',
        'env_path' => 'env.testing.php',
        'config_path' => 'config/biz.php',
    ];

    public function _initialize()
    {
        $envFilePath = Configuration::projectDir().$this->config['env_path'];
        if (!file_exists($envFilePath)) {
            throw new ModuleRequireException(
                __CLASS__,
                "Biz env file not found in {$envFilePath} \n\n".
                "Please specify path to bootstrap file using `env_path` config option\n \n"
            );
        }
        Env::load(require $envFilePath);

        $bizConfigFilePath = Configuration::projectDir().$this->config['config_path'];
        if (!file_exists($bizConfigFilePath)) {
            throw new ModuleRequireException(
                __CLASS__,
                "Biz config file not found in {$bizConfigFilePath} \n\n".
                "Please specify path to bootstrap file using `config_path` config option\n \n"
            );
        }
        $this->bizConfig = require $bizConfigFilePath;

        if (!class_exists($this->config['class'])) {
            throw new ModuleRequireException(
                __CLASS__,
                "Biz class {$this->config['class']} \n\n".
                "Please specify biz class using `class` config option\n\n"
            );
        }
    }

    public function biz()
    {
        return $this->biz;
    }

    public function createService($service)
    {
        return $this->biz->service($service);
    }

    public function createDao($dao)
    {
        return $this->biz->dao($dao);
    }

    /**
     * Inserts an SQL record into a database. This record will be erased after the test.
     *
     * ```php
     * <?php
     * $I->haveInDatabase('users', array('name' => 'hello', 'email' => 'hello@example.com'));
     * ?>
     * ```
     *
     * @param string $table
     * @param array  $data
     *
     * @return int $id
     */
    public function haveInDatabase($table, array $data)
    {
        return $this->biz['db']->insert($table, $data);
    }

    public function seeInDatabase($table, $criteria = [])
    {
        $res = $this->countInDatabase($table, $criteria);
        $this->assertGreaterThan(
            0,
            $res,
            'No matching records found for criteria '.json_encode($criteria).' in table '.$table
        );
    }

    /**
     * Asserts that the given number of records were found in the database.
     *
     * ```php
     * <?php
     * $I->seeNumRecords(1, 'users', ['name' => 'davert'])
     * ?>
     * ```
     *
     * @param int    $expectedNumber Expected number
     * @param string $table          Table name
     * @param array  $criteria       Search criteria [Optional]
     */
    public function seeNumRecords($expectedNumber, $table, array $criteria = [])
    {
        $actualNumber = $this->countInDatabase($table, $criteria);
        $this->assertEquals(
            $expectedNumber,
            $actualNumber,
            sprintf(
                'The number of found rows (%d) does not match expected number %d for criteria %s in table %s',
                $actualNumber,
                $expectedNumber,
                json_encode($criteria),
                $table
            )
        );
    }

    public function dontSeeInDatabase($table, $criteria = [])
    {
        $count = $this->countInDatabase($table, $criteria);
        $this->assertLessThan(
            1,
            $count,
            'Unexpectedly found matching records for criteria '.json_encode($criteria).' in table '.$table
        );
    }

    public function grabFromDatabase($table, $column, $criteria = [])
    {
        return $this->proceedSeeInDatabase($table, $column, $criteria);
    }

    /**
     * Returns the number of rows in a database
     *
     * @param string $table    Table name
     * @param array  $criteria Search criteria [Optional]
     *
     * @return int
     */
    public function grabNumRecords($table, array $criteria = [])
    {
        return $this->countInDatabase($table, $criteria);
    }

    /**
     * Update an SQL record into a database.
     *
     * ```php
     * <?php
     * $I->updateInDatabase('users', array('isAdmin' => true), array('email' => 'miles@davis.com'));
     * ?>
     * ```
     *
     * @param string $table
     * @param array  $data
     * @param array  $criteria
     */
    public function updateInDatabase($table, array $data, array $criteria = [])
    {
        $queryBuilder
            ->update($table)
            ->values($data);

        $index = 0;
        foreach ($criteria as $field => $value) {
            $builder->andWhere("{$field} = :{$field}");
            $builder->setParameter(":{$field}", $value);
        }

        $this->debugSection('Query', $builder->getSQL());
        $this->debugSection('Parameters', $criteria);

        $builder->execute();
    }

    protected function countInDatabase($table, $criteria = [])
    {
        return (int) $this->proceedSeeInDatabase($table, 'COUNT(*)', $criteria);
    }

    protected function proceedSeeInDatabase($table, $column, $criteria)
    {
        $builder = $this->biz['db']->createQueryBuilder()
            ->select($column)
            ->from($table);

        $index = 0;
        foreach ($criteria as $field => $value) {
            $builder->andWhere("{$field} = :{$field}");
            $builder->setParameter(":{$field}", $value);
        }

        $this->debugSection('Query', $builder->getSQL());
        $this->debugSection('Parameters', $criteria);

        return $builder->execute()->fetchColumn(0);
    }

    public function _before(\Codeception\TestInterface $test)
    {
        $this->biz = new $this->config['class']($this->bizConfig);
        $this->biz->boot();

        if (isset($this->biz['db'])) {
            $this->biz['db']->beginTransaction();
        }

        if (isset($this->biz['redis'])) {
            $this->biz['redis']->flushDB();
        }

        parent::_before($test);
    }

    public function _after(\Codeception\TestInterface $test)
    {
        if (isset($this->biz['db'])) {
            $this->biz['db']->rollBack();
        }

        if (isset($this->biz['redis'])) {
            $this->biz['redis']->flushDB();
        }

        unset($this->biz);
        parent::_after($test);
    }
}
