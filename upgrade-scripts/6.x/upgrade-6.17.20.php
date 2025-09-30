<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme();
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
    }

    private function updateScheme()
    {
        if (!$this->isFieldExist('setting', 'namespace')) {
            $this->getConnection()->exec("ALTER TABLE `setting` ADD `namespace` varchar(255) NOT NULL DEFAULT 'default' ");
        }

        if (!$this->isTableExist('block_template')) {
            $this->getConnection()->exec("CREATE TABLE `block_template` (
              `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '模版ID',
              `title` varchar(255) NOT NULL COMMENT '标题',
              `mode` ENUM('html','template') NOT NULL DEFAULT 'html' COMMENT '模式' ,
              `template` text COMMENT '模板',
              `templateName` VARCHAR(255)  COMMENT '编辑区模板名字',
              `templateData` text  COMMENT '模板数据',
              `content` text COMMENT '默认内容',
              `data` text COMMENT '编辑区内容',
              `code` varchar(255) NOT NULL DEFAULT '' COMMENT '编辑区编码',
              `meta` text  COMMENT '编辑区元信息',
              `tips` VARCHAR( 255 ),
              `category` varchar(60) NOT NULL DEFAULT 'system' COMMENT '分类(系统/主题)',
              `createdTime` int(11) UNSIGNED NOT NULL COMMENT '创建时间',
              `updateTime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `code` (`code`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='编辑区模板'; ");
        }

        if (!$this->isFieldExist('block', 'orgId')) {
            $this->getConnection()->exec("ALTER TABLE  `block` ADD  `orgId` INT(11) NOT NULL  DEFAULT 1 COMMENT '组织机构Id'");
        }

        if (!$this->isFieldExist('block', 'blockTemplateId')) {
            $this->getConnection()->exec("ALTER TABLE  `block` ADD  `blockTemplateId` INT(11) NOT NULL COMMENT '模版ID'");
        }
        if ($this->isFieldExist('block', 'title')) {
            $this->getConnection()->exec("INSERT INTO `block_template`( `title`, `mode`,`template`,`templateName`,`templateData`,`content`,`data`,`code`, `meta`, `tips`, `category`, `createdTime`,`updateTime`) select `title`, `mode`,`template`,`templateName`,`templateData`,`content`,`data`,`code`, `meta`, `tips`, `category`, `createdTime`,`updateTime` from `block`; ");
        }

        $this->getConnection()->exec("UPDATE `block` join `block_template` on block.code = block_template.code SET block.blockTemplateId = block_template.id;");

        if ($this->isIndexExist('block', 'code', 'code')) {
            $this->getConnection()->exec("ALTER TABLE `block` DROP INDEX `code`");
        }

        if ($this->isFieldExist('block', 'mode')) {
            $this->getConnection()->exec("ALTER TABLE `block` DROP `mode`, DROP `template`, DROP `title`, DROP `templateName`, DROP `templateData`, DROP `meta`, DROP `tips`, DROP `category`");
        }

        if ($this->isIndexExist('setting', 'name', 'name')) {
            $this->getConnection()->exec("ALTER TABLE `setting` DROP INDEX `name`");
        }

        if (!$this->isIndexExist('setting', 'namespace', 'name')) {
            $this->getConnection()->exec("ALTER TABLE `setting` ADD UNIQUE KEY(`name`, `namespace`)");
        }

        $this->getConnection()->exec("delete from cache;");
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql    = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function s3isIndexExist($name, $table)
    {
        $sql    = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        var_dump($result);
        return empty($result) ? false : true;
    }

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql    = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isCrontabJobExist($code)
    {
        $sql    = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isblockTemplateEmpty()
    {
        $sql    = "select * from block_template";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? true : false;
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
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
