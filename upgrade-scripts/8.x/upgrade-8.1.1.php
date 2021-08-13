<?php

use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{

    const VERSION = '8.1.1';

    public function __construct($biz)
    {
        parent::__construct($biz);
    }

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme($index);

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

        try {
            $dir = realpath($this->biz['kernel.root_dir'].'/../web/install');
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $funcNames = array(
            1 => 'addOrderUserIdIndex',
            2 => 'addInviteRecordInviteUserIdIndex',
            3 => 'addInviteRecordFields',
            4 => 'updateOrderInfo',
            5 => 'addInviteRecordOrderInfoJob',
        );

        if ($index == 0) {
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

        if ($page == 1) {
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

    protected function addOrderUserIdIndex()
    {
        if (!$this->isIndexExist('orders', 'userId', 'idx_userId')) {
            $sql = 'ALTER TABLE `orders` ADD INDEX idx_userId (`userId`)';
            $this->getConnection()->exec($sql);
            $this->logger(self::VERSION, 'info', 'orders 增加索引 idx_userId - 成功');
        }

        return 1;  
    }

    protected function addInviteRecordInviteUserIdIndex()
    {
        if (!$this->isIndexExist('invite_record', 'inviteUserId', 'idx_inviteUserId')) {
            $sql = 'ALTER TABLE `invite_record` ADD INDEX idx_inviteUserId ( `inviteUserId` )';
            $this->getConnection()->exec($sql);
            $this->logger(self::VERSION, 'info', 'invite_record 增加索引 idx_inviteUserId - 成功');
        }

        return 1; 
    }

    protected function addInviteRecordFields()
    {
        if (!$this->isFieldExist('invite_record', 'amount')) {
            $this->getConnection()->exec("ALTER TABLE `invite_record` ADD COLUMN `amount` float(10,2) NOT NULL DEFAULT '0' COMMENT '被邀请者被邀请后的消费总额'");
        }

        if (!$this->isFieldExist('invite_record', 'cashAmount')) {
            $this->getConnection()->exec("ALTER TABLE `invite_record` ADD COLUMN `cashAmount`  float(10,2) NOT NULL DEFAULT '0' COMMENT '被邀请者被邀请后的现金消费总额'");
        }

        if (!$this->isFieldExist('invite_record', 'coinAmount')) {
            $this->getConnection()->exec("ALTER TABLE `invite_record` ADD COLUMN `coinAmount`  float(10,2) NOT NULL DEFAULT '0' COMMENT '被邀请者被邀请后的虚拟币消费总额'");    
        }

        return 1;
    }

    protected function addInviteRecordOrderInfoJob($page)
    {
        $time = time();

        if (!$this->isCrontabJobExist('UpdateInviteRecordOrderInfoJob')) {
            $this->getConnection()->exec(
                "INSERT INTO `job`
                (`name`, `source`, `expression`, `class`, `args`, `misfire_policy`, `updated_time`, `created_time`) 
                VALUES 
                ('UpdateInviteRecordOrderInfoJob', 'MAIN', '0 * * * *', 'Biz\\\\User\\\\Job\\\\UpdateInviteRecordOrderInfoJob', '', 'missed', {$time}, {$time});
                "
            );
        }

        return 1;
    }

    protected function updateOrderInfo($page)
    {
        $pageCount = 300;
        $start = ($page - 1) * $pageCount;
        $limit = $page * $pageCount;
        $count = $this->getInviteRecordService()->countRecords(array());

        if ($start > $count) {
            return 1;
        }
        $this->getInviteRecordService()->flushOrderInfo(array(), $start, $limit);

        return ++$page;
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
        $sql = "select * from job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function getInviteRecordService()
    {
        return $this->createService('User:InviteRecordService');
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question:QuestionDao');
    }

    protected function getFileUsedDao()
    {
        return $this->createDao('File:FileUsedDao');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Service\SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Dao\JobDao
     */
    protected function getJobDao()
    {
        return $this->createDao('Scheduler:JobDao');
    }

    /**
     * @return \Biz\CloudPlatform\Service\AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
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
        $data = date('Y-m-d H:i:s')." [{$level}] {$version} ".$message.PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'].'/../app/logs/upgrade.log';
    }
}
