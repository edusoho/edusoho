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

        $connection->exec("
            ALTER TABLE `course` ADD `deadlineNotify` ENUM('active','none') NOT NULL DEFAULT 'none' COMMENT '开启有效期通知' AFTER `userId`, ADD `daysOfNotifyBeforeDeadline` INT(10) NOT NULL DEFAULT '0' AFTER `deadlineNotify`;
        ");

        $connection->exec("
            ALTER TABLE `course_member` ADD `deadlineNotified` INT(10) NOT NULL DEFAULT '0' AFTER `locked`;
        ");
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