<?php

use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme($index);

            $this->getConnection()->commit();

            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath($this->biz['kernel.root_dir'] . "/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set("crontab_next_executed_time", time());
    }

    private function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);
        clearstatcache(true);
        sleep(3);
        //注解需要该目录存在
        if (!$filesystem->exists($cachePath.'/annotations/topxia')) {
            $filesystem->mkdir($cachePath.'/annotations/topxia');
        }
    }

    private function updateScheme($index)
    {

        switch ($index) {
            case 0:
                $this->deleteCache();
                $this->updateDB1();
                break;
            case 1:
                if ($this->isFieldExist('question', 'userId')) {
                    $this->getConnection()->exec("ALTER TABLE question CHANGE `userId` `createdUserId` INT(10) UNSIGNED NOT NULL DEFAULT '0'");
                }

                break;
            case 2:
                if (!$this->isFieldExist('question', 'updatedUserId')) {
                    $this->getConnection()->exec("ALTER TABLE question ADD COLUMN updatedUserId int(10) UNSIGNED NOT NULL DEFAULT '0' AFTER createdUserId");
                }
                break;
            case 3:
                $this->getConnection()->exec("UPDATE question SET updatedUserId = createdUserId");
                break;
            default:
                $index = -1;
        }

        if ($index !== -1) {
            $index++;
            return array(
                'index' => $index,
                'message' => '正在升级数据库',
            );
        }

        return null;

    }

    private function updateDB1()
    {
        if (!$this->isTableExist('reward_point_account')) {
            $this->getConnection()->exec("
             CREATE TABLE `reward_point_account` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `userId` int(10) UNSIGNED NOT NULL COMMENT '用户Id',
              `balance` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '积分余额',
              `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
              `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分账户';
            ");
        }
        if (!$this->isTableExist('reward_point_account_flow')) {
            $this->getConnection()->exec("
                 CREATE TABLE `reward_point_account_flow` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
                  `sn` bigint(20) unsigned NOT NULL COMMENT '账目流水号',
                  `type` varchar(32) NOT NULL DEFAULT '' COMMENT 'inflow, outflow',
                  `way` varchar(255) NOT NULL DEFAULT '' COMMENt '积分获取方式',
                  `amount` int(10) NOT NULL DEFAULT 0 COMMENT '金额(积分)',
                  `name` varchar(1024) NOT NULL DEFAULT '' COMMENT '帐目名称',
                  `operator` int(10) unsigned NOT NULL COMMENT '操作员ID',
                  `targetId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '流水所属对象ID',
                  `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '流水所属对象类型',
                  `note` varchar(255) NOT NULL DEFAULT '',
                  `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                  `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分帐目流水';
            ");
        }

        if (!$this->isTableExist('reward_point_product')) {
            $this->getConnection()->exec("
                CREATE TABLE `reward_point_product` (
                  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `title` varchar(60) NOT NULL DEFAULT '' COMMENT '商品名称',
                  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
                  `price` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '兑换价格（积分）',
                  `about` text COMMENT '简介',
                  `requireConsignee` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '需要收货人',
                  `requireTelephone` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '需要联系电话',
                  `requireEmail` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '需要邮箱',
                  `requireAddress` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '需要地址',
                  `status` varchar(32) DEFAULT 'draft' COMMENT '商品状态  draft|published',
                  `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                  `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist("reward_point_product_order")) {
            $this->getConnection()->exec("
                 CREATE TABLE `reward_point_product_order` (
                  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `sn` varchar(60) NOT NULL DEFAULT '' COMMENT '订单号',
                  `productId` int(10) UNSIGNED NOT NULL COMMENT '商品Id',
                  `title` varchar(60) NOT NULL DEFAULT '' COMMENT '订单名称',
                  `price` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '兑换价格（积分）',
                  `userId` int(10) UNSIGNED NOT NULL COMMENT '用户Id',
                  `consignee` varchar(128) NOT NULL DEFAULT '' COMMENT '收货人',
                  `telephone` varchar(20) NOT NULL DEFAULT '' COMMENT '联系电话',
                  `email` varchar(50) NOT NULL DEFAULT '' COMMENT '邮箱',
                  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '需要地址',
                  `sendTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                  `message` varchar(100) NOT NULL DEFAULT '' COMMENT '发货留言',
                  `status` varchar(32) DEFAULT 'created' COMMENT '发货状态  created|sending|finished',
                  `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                  `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isFieldExist('course_v8', 'rewardPoint')) {
            $this->getConnection()->exec(" ALTER TABLE `course_v8`   ADD COLUMN `rewardPoint` INT(10) NOT NULL DEFAULT 0 COMMENT '课程积分'");
        }

        if (!$this->isFieldExist('course_v8', 'taskRewardPoint')) {
            $this->getConnection()->exec(" ALTER TABLE `course_v8`     ADD COLUMN `taskRewardPoint` INT(10) NOT NULL DEFAULT 0 COMMENT '任务积分'; ");
        }

        $this->getConnection()->exec(" TRUNCATE TABLE `question_marker_result`;");

        if ($this->isFieldExist('question_marker_result', 'lessonId')) {
            $this->getConnection()->exec("ALTER TABLE `question_marker_result` CHANGE `lessonId` `taskId` INT(10) UNSIGNED NOT NULL DEFAULT '0';");
        }

        if (!$this->isIndexExist("question_marker_result", 'questionMarkerId', 'idx_qmid_taskid_stats')) {
            $this->getConnection()->exec("ALTER TABLE `question_marker_result` ADD INDEX `idx_qmid_taskid_stats` (`questionMarkerId`, `taskId`, `status`);");
        }

        if (!$this->isIndexExist("question_marker_result", 'questionMarkerId', 'idx_qmid_taskid_stats')) {
            $this->getConnection()->exec("ALTER TABLE `question_marker_result` ADD INDEX `idx_qmid_taskid_stats` (`questionMarkerId`, `taskId`, `status`);");
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isCrontabJobExist($code)
    {
        $sql = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}

abstract class AbstractUpdater
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function getConnection()
    {
        return $this->biz['db'];
    }

    protected function createService($name)
    {
        return $this->biz->service($name);
    }

    protected function createDao($name)
    {
        return $this->biz->dao($name);
    }

    abstract public function update();
}
