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
            $this->updateDefaultPicture();

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

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);


     }

     private function updateDefaultPicture()
     {
        $default = $this->getSettingService()->get("default", array());
        if($default["defaultCoursePictureFileName"]) {
            $default["course.png"] = 'assets/img/default/large'.$default["defaultCoursePictureFileName"];
        }
        if($default["defaultAvatarFileName"]) {
            $default["avatar.png"] = 'assets/img/default/large'.$default["defaultAvatarFileName"];
        }

        $this->getSettingService()->set("default", $default);

     }

     private function  authSetting()
     {
        $auth = $this->getSettingService()->get("auth", array());

        if($auth['register_mode'] == "opened") {
            $auth['register_mode'] = 'email';
        }

        $this->getSettingService()->set("auth", $auth);
     }

     private function getSettingService()
     {
        return ServiceKernel::instance()->createService('System.SettingService');
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