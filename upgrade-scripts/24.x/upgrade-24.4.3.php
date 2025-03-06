<?php

use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function __construct($biz)
    {
        parent::__construct($biz);
    }

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme((int)$index);
            $this->getConnection()->commit();
            if (!empty($result)) {
                return $result;
            } else {
                $this->logger('info', '执行升级脚本结束');
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            $this->logger('error', $e->getTraceAsString());
            throw $e;
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;
        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = [
            'createItemBankExerciseBind',
            'createItemBankExerciseAutoJoinRecord',
            'alterItemBankExerciseMember',
            'alterItemBankExercise',
            'updateItemBankExercise',
            'updateItemBankExerciseMember'
	];
        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }
        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');

            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $funcNames[$step];

        $page = $this->$method($page);
        if (1 == $page) {
            ++$step;
        }
        if ($step <= count($funcNames)) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }
    }

    protected function createItemBankExerciseBind()
    {
        if (!$this->isTableExist('item_bank_exercise_bind')) {
            $this->logger('info', '开始创建表item_bank_exercise_bind');
            $this->getConnection()->exec("
              CREATE TABLE `item_bank_exercise_bind` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `bindId` int(11) NOT NULL,
              `bindType` varchar(64) NOT NULL COMMENT '绑定类型classroom, course',
              `itemBankExerciseId` int(11) NOT NULL,
              `seq` int(11) NOT NULL COMMENT '顺序',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `idx_itemBankExerciseId` (`itemBankExerciseId`),
              UNIQUE KEY `uniq_bindType_bindId_itemBankExerciseId` (`bindType`, `bindId`, `itemBankExerciseId`),
              KEY `idx_bindType_bindId` (`bindType`, `bindId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
            $this->logger('info', '创建表item_bank_exercise_bind成功');
        }

        return 1;
    }

    protected function createItemBankExerciseAutoJoinRecord()
    {
        if (!$this->isTableExist('item_bank_exercise_auto_join_record')) {
            $this->logger('info', '开始创建表item_bank_exercise_auto_join_record');
            $this->getConnection()->exec("
              CREATE TABLE `item_bank_exercise_auto_join_record`  (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `userId` int(10) NOT NULL,
              `itemBankExerciseId` int(11) NOT NULL,
              `itemBankExerciseBindId` int(11) NOT NULL,
              `isValid` tinyint(1) DEFAULT '1' COMMENT '是否有效',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `idx_itemBankExerciseId` (`itemBankExerciseId`),
              KEY `idx_itemBankExerciseBindId` (`itemBankExerciseBindId`),
              UNIQUE KEY `uniq_userId_itemBankExerciseId_itemBankExerciseBindId` (`userId`, `itemBankExerciseId`, `itemBankExerciseBindId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
            $this->logger('info', '创建表item_bank_exercise_auto_join_record成功');
        }

        return 1;
    }

    protected function alterItemBankExerciseMember()
    {
        if (!$this->isFieldExist('item_bank_exercise_member','canLearn')) {
            $this->logger('info', '开始创建canLearn字段');
            $this->getConnection()->exec("
              ALTER TABLE `item_bank_exercise_member` ADD COLUMN `canLearn` tinyint(1) NOT NULL DEFAULT '1' COMMENT '可以学习' AFTER `deadlineNotified`;

        ");
            $this->logger('info', '创建canLearn字段成功');
        }
        if (!$this->isFieldExist('item_bank_exercise_member', 'joinedChannel')) {
            $this->logger('info', '开始创建joinedChannel字段');
            $this->getConnection()->exec("
              ALTER TABLE `item_bank_exercise_member` ADD COLUMN `joinedChannel` varchar(255) NOT NULL DEFAULT '' COMMENT '加入来源' AFTER `canLearn`;

        ");
            $this->logger('info', '创建joinedChannel字段成功');
        }

        return 1;
    }

    protected function alterItemBankExercise()
    {
        if (!$this->isFieldExist('item_bank_exercise', 'updated_user_id')) {
            $this->logger('info', '开始创建updated_user_id字段');
            $this->getConnection()->exec("
              ALTER TABLE `item_bank_exercise` ADD COLUMN `updated_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新人' AFTER `creator`;
        ");
            $this->logger('info', '创建updated_user_id字段成功');
        }

        return 1;
    }

    protected function updateItemBankExercise()
    {
        if ($this->isFieldExist('item_bank_exercise', 'updated_user_id')) {
            $this->logger('info', '开始更新updated_user_id数据');
            $this->getConnection()->exec("
              UPDATE `item_bank_exercise` SET `updated_user_id` = `creator`;
        ");
            $this->logger('info', '更新updated_user_id数据成功');
        }

        return 1;
    }

    protected function updateItemBankExerciseMember()
    {
        if ($this->isFieldExist('item_bank_exercise_member', 'joinedChannel')) {
            $this->logger('info', '开始更新joinedChannel数据');
            $this->getConnection()->exec("
                UPDATE `item_bank_exercise_member` SET `joinedChannel` = 'free_join' where orderId = 0;
            ");
            $this->getConnection()->exec("
                UPDATE `item_bank_exercise_member` SET `joinedChannel` = 'buy_join' where orderId != 0;
            ");
            $this->getConnection()->exec("
                UPDATE `item_bank_exercise_member` SET `joinedChannel` = 'free_join' where remark = 'site.join_by_free';
            ");
            $this->getConnection()->exec("
                UPDATE `item_bank_exercise_member` SET `joinedChannel` = 'buy_join' where remark = 'site.join_by_purchase';
            ");
            $this->logger('info', '更新joinedChannel数据成功');
        }

        return 1;
    }

    protected function generateIndex($step, $page)
    {
        return $step * 1000000 + $page;
    }

    protected function getStepAndPage($index)
    {
        $step = intval($index / 1000000);
        $page = $index % 1000000;

        return array($step, $page);
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return !empty($result);
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return !empty($result);
    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where key_name='{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return !empty($result);
    }

    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
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

    protected function logger($level, $message)
    {
        $version = \AppBundle\System::VERSION;
        $data = date('Y-m-d H:i:s') . " [{$level}] {$version} " . $message . PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'] . '/../app/logs/upgrade.log';
    }

    protected function getAppLogDao()
    {
        return $this->createDao('CloudPlatform:CloudAppLogDao');
    }
}
