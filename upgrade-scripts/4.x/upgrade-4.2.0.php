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
            ALTER TABLE `course_lesson_learn` ADD `learnTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `finishedTime`;
        ");

        $connection->exec("
            ALTER TABLE `course_lesson_learn` ADD `watchTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `learnTime`, ADD `videoStatus` ENUM('paused','playing') NOT NULL DEFAULT 'paused' AFTER `watchTime`;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `course_draft` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `title` varchar(255) NOT NULL,
              `summary` text ,
              `courseId` int(10) unsigned NOT NULL,
              `content` text ,
              `userId` int(10) unsigned NOT NULL,
              `lessonId` int(10) unsigned NOT NULL,
              `createdTime` int(10) unsigned NOT NULL,
              PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
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