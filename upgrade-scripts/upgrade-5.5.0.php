<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;

 class EduSohoUpgrade extends AbstractUpdater
 {
     public function update()
     {
        $this->getConnection()->beginTransaction();
        try{
            $this->authSetting();

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

        $developerSetting = ServiceKernel::instance()->createService('System.SettingService')->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);


     }

     private function  authSetting(){
        //query
        $sql = "SELECT value FROM setting WHERE name='auth'";
        $result = $this->getConnection()->fetchColumn($sql);

        //update value
        $dateser = unserialize($result);
        $dateser['register_mode'] ='email';
        $value = serialize($dateser);
        //update to db
        $sql = "UPDATE setting SET value ='{$value}' where  name='auth'";
        $this->getConnection()->exec( $sql);
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