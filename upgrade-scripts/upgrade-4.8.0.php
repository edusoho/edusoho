<?php

use Symfony\Component\Filesystem\Filesystem;

 class EduSohoUpgrade extends AbstractUpdater
 {
     public function update()
     {
        $this->getConnection()->beginTransaction();
        try{
            $this->updateScheme();
            $this->updateDefaultSetting();

            $this->getConnection()->commit();
        } catch(\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }
     }

     private function updateScheme()
     {
        $connection = $this->getConnection();
        $connection->exec("ALTER TABLE `orders` CHANGE `totalPrice` `totalPrice` FLOAT(10,2) NOT NULL DEFAULT '0';");
        $connection->exec("ALTER TABLE `orders` CHANGE `coinAmount` `coinAmount` FLOAT(10,2) NOT NULL DEFAULT '0';");
     }

     private function updateDefaultSetting()
     {

        $settingService = $this->createService('System.SettingService');

        $defaultSetting = array();
        $defaultSetting['user_name'] ='学员';
        $defaultSetting['chapter_name'] ='章';
        $defaultSetting['part_name'] ='节';

        $default = $settingService->get('default', array());
        $defaultSetting = array_merge($default, $defaultSetting);

        $settingService->set('default', $defaultSetting);

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