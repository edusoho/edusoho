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

        $connection->exec("ALTER TABLE `orders` CHANGE `payment` `payment` ENUM('none','alipay','tenpay','coin','wxpay','heepay') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
        $connection->exec("ALTER TABLE `cash_flow` CHANGE `payment` `payment` ENUM('alipay','wxpay','heepay') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
        $connection->exec("ALTER TABLE `cash_orders` CHANGE `payment` `payment` ENUM('none','alipay','wxpay','heepay') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");

        if (!$this->isFieldExist('orders', 'token')) {
            $connection->exec("ALTER TABLE `orders` ADD `token` VARCHAR(50) NULL DEFAULT NULL COMMENT '令牌'");
        }

        $connection->exec(" ALTER TABLE `orders` CHANGE `payment` `payment` ENUM('none','alipay','tenpay','coin','wxpay','heepay','quickpay') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
        $connection->exec(" ALTER TABLE `cash_orders` CHANGE `payment` `payment` ENUM('none','alipay','wxpay','heepay','quickpay') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
        $connection->exec(" ALTER TABLE `cash_flow` CHANGE `payment` `payment` ENUM('alipay','wxpay','heepay','quickpay') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `user_pay_agreement` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `userId` int(11) NOT NULL COMMENT '用户Id',
            `type` int(8) NOT NULL DEFAULT '0' COMMENT '0:储蓄卡1:信用卡',
            `bankName` varchar(255) NOT NULL COMMENT '银行名称',
            `bankNumber` int(8) NOT NULL COMMENT '银行卡号',
            `userAuth` varchar(225) DEFAULT NULL COMMENT '用户授权',
            `bankAuth` varchar(225) NOT NULL COMMENT '银行授权码',
            `bankId` int(8) NOT NULL COMMENT '对应的银行Id',
            `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
            `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户授权银行'"
        );

        $connection->exec("delete from  block where code='bill_banner'");
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
