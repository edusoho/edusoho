<?php

use Symfony\Component\Filesystem\Filesystem;

 class EduSohoUpgrade extends AbstractUpdater
 {
     public function update()
     {
        $this->getConnection()->beginTransaction();
        try{
            $this->updateScheme();

            $this->getConnection()->commit();
        } catch(\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }
     }

    private function updateScheme()
     {
        $connection = $this->getConnection();

        
        if(!$this->isFieldExist('status', 'private')) {
            $connection->exec("ALTER TABLE `status` ADD `private` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否私有' AFTER `likeNum`;");
        }
        if(!$this->isFieldExist('course_thread', 'private')) {
            $connection->exec("ALTER TABLE `course_thread` ADD `private` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否私有' AFTER `isClosed`;");
        }
        if(!$this->isFieldExist('course_review', 'private')) {
            $connection->exec("ALTER TABLE `course_review` ADD `private` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否私有' AFTER `rating`;");
            $connection->exec("update course_lesson_learn set learnTime = learnTime*60, watchTime = watchTime*60;");
        }


    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
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