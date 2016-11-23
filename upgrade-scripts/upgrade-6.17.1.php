<?php

use Topxia\Common\BlockToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->batchUpdate($index);
            $this->getConnection()->commit();

            $this->updateCrontabSetting();

            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('Crontab.CrontabService')->setNextExcutedTime(time());
    }

    private function updateScheme()
    {
        if (!$this->isTableExist('dictionary_item')) {
            $this->getConnection()->exec("CREATE TABLE `dictionary_item` (
             `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
             `type` varchar(255) NOT NULL COMMENT '字典类型',
             `code` varchar(64) DEFAULT NULL COMMENT '编码',
             `name` varchar(255) NOT NULL COMMENT '字典内容名称',
             `weight` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
             `createdTime` int(10) unsigned NOT NULL,
             `updateTime` int(10) unsigned DEFAULT '0',
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
            ");

            $this->getConnection()->exec("INSERT INTO `dictionary_item` (`type`, `code`, `name`, `weight`, `createdTime`, `updateTime`) VALUES ('refund_reason', NULL, '课程内容质量差', '0', '0', '0');");

            $this->getConnection()->exec("INSERT INTO `dictionary_item` (`type`, `code`, `name`, `weight`, `createdTime`, `updateTime`) VALUES ('refund_reason', NULL, '老师服务态度不好', '0', '0', '0');");
        }

        if (!$this->isTableExist('dictionary')) {
            $this->getConnection()->exec("CREATE TABLE `dictionary` (
                         `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                         `name` varchar(255) NOT NULL COMMENT '字典名称',
                         `type` varchar(255) NOT NULL COMMENT '字典类型',
                         PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");

            $this->getConnection()->exec("INSERT INTO `dictionary` (`name`, `type`) VALUES ('退学原因', 'refund_reason');");
        }

        $blockMeta = json_decode(file_get_contents(realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/themes/jianmo/block.json")), true);
        $this->updateBlock("jianmo:middle_banner", $blockMeta["jianmo:middle_banner"]);
        $this->updateBlock("jianmo:advertisement_banner", $blockMeta["jianmo:advertisement_banner"]);
    }

    private function batchUpdate($index)
    {
        if ($index === 0) {
            $this->updateScheme();
            return array(
                'index'    => 1,
                'message'  => '正在升级数据...',
                'progress' => 0
            );
        }

        $conditions = array(
            'storage'  => 'cloud',
            'globalId' => 0
        );
        $total   = $this->getUploadFileService()->searchFileCount($conditions);
        $maxPage = ceil($total / 100) ? ceil($total / 100) : 1;

        $this->getCloudFileService()->synData($conditions);

        if ($index <= $maxPage) {
            return array(
                'index'    => $index + 1,
                'message'  => '正在升级数据...',
                'progress' => 0
            );
        }
    }

    public function updateBlock($code, $meta)
    {
        global $kernel;
        $block = $this->getBlockService()->getBlockByCode($code);

        $default = array();
        foreach ($meta['items'] as $i => $item) {
            $default[$i] = $item['default'];
        }

        if (empty($block)) {
            $block = $this->getBlockService()->createBlock(array(
                'code'         => $code,
                'mode'         => 'template',
                'category'     => empty($meta['category']) ? 'system' : $meta['category'],
                'meta'         => $meta,
                'data'         => $default,
                'templateName' => $meta['templateName'],
                'title'        => $meta['title'],
                'content'      => ''
            ));
            $html = BlockToolkit::render($block, $kernel->getContainer());

            $block = $this->getBlockService()->updateBlock($block['id'], array(
                'content' => $html
            ));
        } else {
            $html  = BlockToolkit::render($block, $kernel->getContainer());
            $block = $this->getBlockService()->updateBlock($block['id'], array(
                'meta'    => $meta,
                'data'    => $block['data'],
                'content' => $html
            ));
        }
    }

    private function updateCrontabSetting()
    {
        $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../app/data/crontab_config.yml");
        $filesystem = new Filesystem();

        if (!empty($dir)) {
            $filesystem->remove($dir);
        }
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

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    private function getCloudFileService()
    {
        return ServiceKernel::instance()->createService('CloudFile.CloudFileService');
    }

    private function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileService');
    }

    private function getBlockService()
    {
        return ServiceKernel::instance()->createService('Content.BlockService');
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
