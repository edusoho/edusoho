<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\BlockToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme();
            $this->updateBlocks();
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = ServiceKernel::instance()->createService('System.SettingService')->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
    }

    private function updateScheme()
    {
        $connection = $this->getConnection();

        if(!$this->isFieldExist('course', 'approval')) {
            $connection->exec("ALTER TABLE `course` ADD COLUMN `approval` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要实名认证';");
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

    

    private function updateBlocks()
    {
        //初始化系统编辑区
        BlockToolkit::init('system', realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/themes/system-block.json"));
        $this->_updateCarouselByCode('bill_banner');
        $this->_updateCarouselByCode('live_top_banner');

        //初始化默认主题编辑区
        BlockToolkit::init('default', realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/themes/default/block.json"));
        $this->_updateCarouselByCode('home_top_banner');

        //初始化清秋主题
        BlockToolkit::init('autumn', realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/themes/autumn/block.json"));
        $this->_updateCarouselByCode('autumn:home_top_banner');

    }

    private function _updateCarouselByCode($code)
    {
        BlockToolkit::updateCarousel($code);
    }

    private function getBlockService()
    {
        return ServiceKernel::instance()->createService('Content.BlockService');
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
    public function __construct($kernel)
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
