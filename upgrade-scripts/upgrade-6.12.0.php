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

        if (!$this->isFieldExist('cash_orders', 'targetType')) {
            $connection->exec("ALTER TABLE `cash_orders` ADD `targetType` VARCHAR(64) NOT NULL DEFAULT 'coin' COMMENT '订单类型'");
        }

        if (!$this->isFieldExist('cash_orders', 'token')) {
            $connection->exec("ALTER TABLE `cash_orders` ADD `token` VARCHAR(50) NULL DEFAULT NULL COMMENT '令牌';");
        }

        if (!$this->isFieldExist('cash_orders', 'data')) {
            $connection->exec("ALTER TABLE `cash_orders` ADD `data` TEXT NULL DEFAULT NULL COMMENT '订单业务数据'");
        }

        $connection->exec("CREATE TABLE IF NOT EXISTS `cash_change` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `userId` int(10) unsigned NOT NULL,
            `amount` double(10,2) NOT NULL DEFAULT '0.00',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

        if (!$this->isTableExist('coupon')) {
            $connection->exec("
                CREATE TABLE `coupon` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `code` varchar(255) NOT NULL COMMENT '优惠码',
                  `type` enum('minus','discount') NOT NULL COMMENT '优惠方式',
                  `status` enum('used','unused','receive') NOT NULL COMMENT '使用状态',
                  `rate` float(10,2) unsigned NOT NULL COMMENT '若优惠方式为打折，则为打折率，若为抵价，则为抵价金额',
                  `batchId` int(10) unsigned  NULL DEFAULT NULL COMMENT '批次号',
                  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用者',
                  `deadline` int(10) unsigned NOT NULL COMMENT '失效时间',
                  `targetType` varchar(64) NUll DEFAULT NULL COMMENT '使用对象类型',
                  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用对象',
                  `orderId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单号',
                  `orderTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
                  `createdTime` int(10) unsigned NOT NULL,
                  `receiveTime` INT(10) unsigned NULL DEFAULT '0'  COMMENT '接收时间',
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='优惠码表';
            ");
        } else {
            $connection->exec("
              ALTER TABLE `coupon` CHANGE `batchId` `batchId` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT '批次号';
            ");

            $connection->exec("
              ALTER TABLE `coupon` CHANGE `targetType` `targetType` varchar(64) NUll DEFAULT NULL COMMENT '使用对象类型';
            ");

            $connection->exec("
              ALTER TABLE `coupon` CHANGE `targetId` `targetId` INT(10) UNSIGNED NULL DEFAULT 0 COMMENT '使用对象';
            ");

            $connection->exec("
              ALTER TABLE `coupon` CHANGE `status` `status` enum('used','unused','receive') NOT NULL COMMENT '使用状态';
            ");

            if (!$this->isFieldExist('coupon', 'receiveTime')) {
                $connection->exec("
                  ALTER TABLE `coupon` ADD `receiveTime` INT(10) NULL DEFAULT 0 COMMENT '接收时间';
                ");
            }
        }

        if (!$this->isTableExist('invite_record')) {
            $connection->exec("
                CREATE TABLE `invite_record` (
                    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `inviteUserId` int(11) unsigned NULL DEFAULT NULL COMMENT '邀请者',
                    `invitedUserId` int(11) unsigned NULL DEFAULT NULL COMMENT '被邀请者',
                    `inviteTime` int(11) unsigned NULL DEFAULT NULL COMMENT '邀请时间',
                    `inviteUserCardId` int(11) unsigned NULL DEFAULT NULL COMMENT '邀请者获得奖励的卡的ID',
                    `invitedUserCardId` int(11) unsigned NULL DEFAULT NULL COMMENT '被邀请者获得奖励的卡的ID',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='邀请记录表';
            ");
        }

        if (!$this->isFieldExist('user', 'inviteCode')) {
            $connection->exec("
                ALTER TABLE `user` ADD `inviteCode` varchar(255) NUll DEFAULT NUll COMMENT '邀请码';
            ");
        }

        if (!$this->isFieldExist('user', 'updatedTime')) {
            $connection->exec("ALTER TABLE `user` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间' AFTER `createdTime`;");
        }

        if (!$this->isFieldExist('course', 'updatedTime')) {
            $connection->exec("ALTER TABLE `course` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间' AFTER `createdTime`;");
        }

        if (!$this->isFieldExist('course_lesson', 'updatedTime')) {
            $connection->exec("ALTER TABLE `course_lesson` ADD `updatedTime` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间' AFTER `createdTime`;");
        }

        if ($this->isFieldExist('user', 'updatedTime')) {
            $connection->exec("UPDATE `user` SET  `updatedTime` = `createdTime`;");
        }

        if ($this->isFieldExist('course', 'updatedTime')) {
            $connection->exec("UPDATE `course` SET  `updatedTime` = `createdTime`;");
        }

        if ($this->isFieldExist('course_lesson', 'updatedTime')) {
            $connection->exec("UPDATE `course_lesson` SET  `updatedTime` = `createdTime`;");
        }

        if (!$this->isIndexExist('user', 'updatedTime')) {
            $connection->exec("ALTER TABLE `user` ADD INDEX `updatedTime` (`updatedTime`);");
        }

        if (!$this->isIndexExist('course', 'updatedTime')) {
            $connection->exec("ALTER TABLE `course` ADD INDEX `updatedTime` (`updatedTime`);");
        }

        if (!$this->isIndexExist('course_lesson', 'updatedTime')) {
            $connection->exec("ALTER TABLE `course_lesson` ADD INDEX `updatedTime` (`updatedTime`);");
        }

        if (!$this->isTableExist('recent_post_num')) {
            $connection->exec(
                "CREATE TABLE `recent_post_num` (
                 `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
                 `ip` varchar(20) NOT NULL COMMENT 'IP',
                 `type` varchar(255) NOT NULL COMMENT '类型',
                 `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'post次数',
                 `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次更新时间',
                 `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                 PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='黑名单表';"
            );
        }

        if (!$this->isTableExist("user_pay_agreement")) {
            $connection->exec("
                CREATE TABLE IF NOT EXISTS `user_pay_agreement` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `userId` int(11) NOT NULL COMMENT '用户Id',
                `type` int(8) NOT NULL DEFAULT '0' COMMENT '0:储蓄卡1:信用卡',
                `bankName` varchar(255) NOT NULL COMMENT '银行名称',
                `bankNumber` int(8) NOT NULL COMMENT '银行卡号',
                `userAuth` varchar(225) DEFAULT NULL COMMENT '用户授权',
                `bankAuth` varchar(225) NOT NULL COMMENT '银行授权码',
                `otherId` int(8) NOT NULL COMMENT '对应的银行Id',
                `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
                 PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户授权银行'"
            );
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

    protected function isIndexExist($table, $indexName)
    {
        $sql    = "show index from `{$table}`  where Key_name='{$indexName}';";
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
