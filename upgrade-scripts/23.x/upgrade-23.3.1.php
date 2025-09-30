<?php

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\ActivityDao;
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
        try {
            $dir = realpath($this->biz['kernel.root_dir'] . '/../web/install');
            $filesystem = new Filesystem();
            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
            $this->logger('error', $e->getTraceAsString());
        }
        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;
        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'createTableBizAnswerRandomSeqRecord',
        );
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

    public function createTableBizAnswerRandomSeqRecord()
    {
        $this->getConnection()->exec("CREATE TABLE IF NOT EXISTS `biz_answer_random_seq_record` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `answer_record_id` INT(10) unsigned NOT NULL COMMENT '答题记录id',
              `items_random_seq` MEDIUMTEXT COMMENT '题目随机顺序，json结构，key是section的id，value是section内试题item_id的顺序列表',
              `options_random_seq` MEDIUMTEXT COMMENT '选项随机顺序，json结构，key是question的id，value是选项的顺序列表',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `answer_record_id` (`answer_record_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        if (!$this->isFieldExist('biz_answer_scene', 'end_time')) {
            $this->getConnection()->exec("ALTER TABLE `biz_answer_scene` ADD COLUMN `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开考截止时间 0表示不限制' after `start_time`;");
        }
        if (!$this->isFieldExist('biz_answer_scene', 'is_items_seq_random')) {
            $this->getConnection()->exec("ALTER TABLE `biz_answer_scene` ADD COLUMN `is_items_seq_random` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启题目乱序' after `end_time`;");
        }
        if (!$this->isFieldExist('biz_answer_scene', 'is_options_seq_random')) {
            $this->getConnection()->exec("ALTER TABLE `biz_answer_scene` ADD COLUMN `is_options_seq_random` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启选项乱序' after `is_items_seq_random`;");
        }
        if (!$this->isFieldExist('biz_answer_record', 'is_items_seq_random')) {
            $this->getConnection()->exec("ALTER TABLE `biz_answer_record` ADD COLUMN `is_items_seq_random` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启题目乱序';");
        }
        if (!$this->isFieldExist('biz_answer_record', 'is_options_seq_random')) {
            $this->getConnection()->exec("ALTER TABLE `biz_answer_record` ADD COLUMN `is_options_seq_random` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启选项乱序';");
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

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function getSettingService()
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
