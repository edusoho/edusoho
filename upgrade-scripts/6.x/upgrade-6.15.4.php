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

            $this->updateCrontabSetting();
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

        if (!$this->isFieldExist('user', 'promotedSeq')) {
            $connection->exec("ALTER TABLE `user` ADD `promotedSeq` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `promoted`;");
        }

        if ($this->isFieldExist('announcement', 'targetId')) {
            $connection->exec("ALTER TABLE `announcement` CHANGE `targetId` `targetId` INT(10) UNSIGNED NOT NULL COMMENT '所属ID';");
        }

        if ($this->isFieldExist('block', 'code')) {
            $connection->exec("ALTER TABLE `block` CHANGE `code` `code` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '编辑区编码';");
        }

        if ($this->isFieldExist('cash_orders', 'payment')) {
            $connection->exec("ALTER TABLE `cash_orders` CHANGE `payment` `payment` ENUM('none','alipay','wxpay','heepay','quickpay','iosiap') NOT NULL DEFAULT 'none';");
        }

        if ($this->isFieldExist('coupon', 'targetId')) {
            $connection->exec("ALTER TABLE `coupon` CHANGE `targetId` `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用对象';");
        }

        if ($this->isFieldExist('coupon', 'receiveTime')) {
            $connection->exec("ALTER TABLE `coupon` CHANGE `receiveTime` `receiveTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接收时间';");
        }

        if ($this->isFieldExist('orders', 'payment')) {
            $connection->exec("ALTER TABLE `orders` CHANGE `payment` `payment` ENUM('none','alipay','tenpay','coin','wxpay','heepay','quickpay','iosiap') NOT NULL DEFAULT 'none' COMMENT '订单支付方式';");
        }

        if ($this->isFieldExist('testpaper_result', 'passedStatus')) {
            $connection->exec("update testpaper_result set passedStatus='none' where passedStatus is null;");
        }

        if ($this->isFieldExist('testpaper_result', 'passedStatus')) {
            $connection->exec("ALTER TABLE `testpaper_result` CHANGE `passedStatus` `passedStatus` enum('none','excellent','good','passed','unpassed') NOT NULL DEFAULT 'none' COMMENT '考试通过状态，none表示该考试没有';");
        }

        if ($this->isFieldExist('thread_member', 'createdTIme')) {
            $connection->exec("ALTER TABLE `thread_member` CHANGE `createdTIme` `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间';");
        }

        if ($this->isFieldExist('upload_files', 'type')) {
            $connection->exec("ALTER TABLE `upload_files` CHANGE `type` `type` enum('document','video','audio','image','ppt','other','flash') NOT NULL DEFAULT 'other' COMMENT '文件类型';");
        }

        if (!$this->isFieldExist('testpaper_item_result', 'pId')) {
            $connection->exec("ALTER TABLE `testpaper_item_result` ADD `pId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '复制试卷题目Id';");
        }

        if ($this->isFieldExist('user_pay_agreement', 'otherId')) {
            $connection->exec("ALTER TABLE `user_pay_agreement` CHANGE `otherId` `bankId` int(8) NOT NULL COMMENT '对应的银行Id';");
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
