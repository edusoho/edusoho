<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\BlockToolkit;

 class EduSohoUpgrade extends AbstractUpdater
 {
     public function update()
     {
        $this->getConnection()->beginTransaction();
        try{
            $this->authSetting();
            $this->updateDefaultPicture();
            $this->updateScheme();
            $this->updateBlocks();

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

    private function updateScheme()
    {
        $connection = $this->getConnection();

        if(!$this->isFileGroupExist('tmp')) {
            $connection->exec("INSERT INTO `file_group` (`name`, `code`, `public`) VALUES ('临时目录', 'tmp', '1');");
        }

        if(!$this->isFileGroupExist('system')) {
            $connection->exec("INSERT INTO `file_group` (`name`, `code`, `public`) VALUES ('全局设置文件', 'system', '1');");
        }

        if(!$this->isFileGroupExist('group')) {
            $connection->exec("INSERT INTO `file_group` (`name`, `code`, `public`) VALUES ('小组', 'group', '1');");
        }

        if(!$this->isFieldExist('block', 'meta')) {
            $connection->exec("ALTER TABLE `block` ADD `meta` TEXT NULL DEFAULT NULL COMMENT '编辑区元信息' AFTER `code`;");
        }

        if(!$this->isFieldExist('block', 'data')) {
            $connection->exec("ALTER TABLE `block` ADD `data` TEXT NULL DEFAULT NULL COMMENT '编辑区内容' AFTER `meta`;");
        }

        if(!$this->isFieldExist('block', 'templateName')) {
            $connection->exec("ALTER TABLE `block` ADD `templateName` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '编辑区模板名字' AFTER `template`;");
        }

        if(!$this->isFieldExist('block_history', 'data')) {
            $connection->exec("ALTER TABLE `block_history` ADD `data` TEXT NULL DEFAULT NULL COMMENT 'block元信息' AFTER `templateData`;");
        }

        if(!$this->isFieldExist('block', 'category')) {
            $connection->exec("ALTER TABLE  `block` ADD   `category` varchar(60) NOT NULL DEFAULT 'system' COMMENT '分类(系统/主题)';");
        }

    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isFileGroupExist($group)
    {
        $sql = "select * from file_group where code='{$group}' ";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    private function updateBlocks()
    {
        global $kernel;

        //初始化系统编辑区
        BlockToolkit::init('system', realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/themes/block.json"), $kernel->getContainer());
        $this->_updateCarouselByCode('bill_banner');
        $this->_updateCarouselByCode('live_top_banner');

        //初始化默认主题编辑区
        BlockToolkit::init('default', realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/themes/default/block.json"), $kernel->getContainer());
        $this->_updateCarouselByCode('home_top_banner');

        //初始化清秋主题
        BlockToolkit::init('autumn', realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/themes/autumn/block.json"), $kernel->getContainer());
        $this->_updateCarouselByCode('autumn:home_top_banner');

    }

    private function _updateCarouselByCode($code)
    {
        BlockToolkit::updateCarousel($code);
    }

     private function updateDefaultPicture()
     {
        $default = $this->getSettingService()->get("default", array());
        if(array_key_exists("defaultCoursePictureFileName", $default) && $default["defaultCoursePictureFileName"]) {
            $filesystem = new Filesystem();
            $rootDir = realpath($this->kernel->getParameter('kernel.root_dir') . '/../');

            $originFile = "{$rootDir}/web/assets/img/default/large".$default["defaultCoursePictureFileName"];
            $targetFile = "{$rootDir}/web/files/2015/05-08/large".$default["defaultCoursePictureFileName"];

            if (file_exists($originFile)) {
                $filesystem->copy($originFile, $targetFile);
                $default["course.png"] = '2015/05-08/large'.$default["defaultCoursePictureFileName"];
            }


        }
        if(array_key_exists("defaultAvatarFileName", $default) && $default["defaultAvatarFileName"]) {
            $filesystem = new Filesystem();
            $rootDir = realpath($this->kernel->getParameter('kernel.root_dir') . '/../');
            $originFile = "{$rootDir}/web/assets/img/default/large".$default["defaultAvatarFileName"];
            $targetFile = "{$rootDir}/web/files/2015/05-08/large".$default["defaultAvatarFileName"];

            if (file_exists($originFile)) {
                $filesystem->copy($originFile, $targetFile);
                $default["avatar.png"] = '2015/05-08/large'.$default["defaultAvatarFileName"];
            }

        }

        $this->getSettingService()->set("default", $default);

     }

     private function  authSetting()
     {
        $auth = $this->getSettingService()->get("auth", array());

        if(array_key_exists("register_mode", $auth) && $auth['register_mode'] == "opened") {
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