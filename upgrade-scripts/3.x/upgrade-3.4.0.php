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
                CREATE TABLE IF NOT EXISTS `course_lesson_view` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `courseId` int(10) NOT NULL,
                `lessonId` int(10) NOT NULL,
                `fileId` int(10) NOT NULL,
                `userId` int(10) NOT NULL,
                `fileType` enum('document','video','audio','image','ppt','other') NOT NULL DEFAULT 'other',
                `fileStorage` enum('local','cloud','net') NOT NULL,
                `fileSource` varchar(32) NOT NULL,
                `createdTime` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
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