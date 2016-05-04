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
        $connection = $this->getConnection();

        $inviteSetting = $this->getSettingService()->get('invite', array());

        $inviteSetting['get_coupon_setting'] = 1;

        $this->getSettingService()->set('invite', $inviteSetting);

        if (!$this->isFieldExist('user_profile', 'isQQPublic')) {
            $connection->exec("ALTER TABLE `user_profile` ADD `isQQPublic` INT NOT NULL DEFAULT '0' AFTER `weixin`;");
        }

        if (!$this->isFieldExist('user_profile', 'isWeixinPublic')) {
            $connection->exec("ALTER TABLE `user_profile` ADD `isWeixinPublic` INT NOT NULL DEFAULT '0' AFTER `isQQPublic`;");
        }

        if (!$this->isFieldExist('user_profile', 'isWeiboPublic')) {
            $connection->exec("ALTER TABLE `user_profile` ADD `isWeiboPublic` INT NOT NULL DEFAULT '0' AFTER `isWeixinPublic`;");
        }
    }

    private function batchUpdate($index)
    {
        if ($index === 0) {
            $this->updateScheme();
            return array(
                'index'    => 1,
                'message'  => '正在升级数据...',
                'progress' => 4.4
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
                'progress' => 4.4
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
                'content'      => $html
            ));

            $html  = BlockToolkit::render($block, $kernel->getContainer());
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
