<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;

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

        try {

            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir))
            $filesystem->remove($dir);

        } catch(\Exception $e) {

        }


     }

    private function updateScheme()
     {
        $connection = $this->getConnection();

        if(!$this->isFieldExist('course', 'approval')) {
            $connection->exec("ALTER TABLE `course` ADD COLUMN `approval` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要实名认证';");
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