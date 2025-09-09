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
            ALTER TABLE `course_lesson_view` CHANGE `fileStorage` `fileStorage` ENUM('local','cloud','net','none') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
        ");

        $connection->exec("
            ALTER TABLE `course_lesson_view` CHANGE `fileType` `fileType` ENUM('document','video','audio','image','ppt','other','none') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'none';
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