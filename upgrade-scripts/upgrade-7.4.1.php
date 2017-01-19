<?php

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->updateScheme();
    }

    private function updateScheme()
    {
        $connection = $this->getConnection();
        if (!$this->isFieldExist('course_member', 'lastViewTime')) {
            $connection->exec("ALTER TABLE `course_member` ADD `lastViewTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后查看时间'");
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }
}

abstract class AbstractUpdater
{
    protected $kernel;

    public function __construct ($kernel)
    {
        $this->kernel = $kernel;
    }

    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    abstract public function update();
}