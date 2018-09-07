<?php

use Symfony\Component\Filesystem\Filesystem;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\CloudAPIFactory;

class EduSohoUpgrade extends AbstractUpdater
{
    private $userUpdateHelper = null;

    public function __construct($biz)
    {
        parent::__construct($biz);

        $this->userUpdateHelper = new BatchUpdateHelper($this->getUserDao());
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

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger('info', '删除缓存');

        return 1;
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'addActivityColumn',
            'updateHomeworkAndExercise',
            'updatePpt',
            'updateLive',
            'updateDownload',
            'updateDoc',
            'updateText',
            'updateAudio',
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }

        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');
            $this->deleteCache();

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

    protected function addActivityColumn()
    {
        if (!$this->isFieldExist('activity', 'finishType')) {
            $this->getConnection()->exec("ALTER TABLE `activity` ADD COLUMN `finishType`  varchar(64)  NOT NULL DEFAULT 'time' COMMENT '任务完成条件类型';");
        }

        if (!$this->isFieldExist('activity', 'finishData')) {
            $this->getConnection()->exec("ALTER TABLE `activity` ADD COLUMN `finishData`  varchar(256)  NOT NULL DEFAULT '1' COMMENT '任务完成条件数据';");
        }

        return 1;
    }

    protected function updateHomeworkAndExercise($page)
    {
        $this->getConnection()->exec("UPDATE `activity` a set finishType = 'submit' where `mediaType` in ('exercise', 'homework');");

        return 1;
    }

    protected function updateFlash($page)
    {
        $this->getConnection()->exec("UPDATE `activity` a ,`activity_flash` flash SET `finishData` = flash.finishDetail , a.`finishType`= 'time' where a.mediaId = flash.id and a.`mediaType` = 'flash';");

        return 1;
    }

    protected function updatePpt($page)
    {
        $this->getConnection()->exec("UPDATE `activity` a ,`activity_ppt` ppt SET `finishData` = ppt.finishDetail , a.`finishType`= ppt.finishType where a.mediaId = ppt.id and a.`mediaType` = 'ppt';");

        return 1;
    }

    protected function updateLive($page)
    {
        $this->getConnection()->exec("UPDATE `activity` SET `finishType` = 'join' where mediaType = 'live';");

        return 1;
    }

    protected function updateDownload($page)
    {
        $this->getConnection()->exec("UPDATE `activity` SET `finishType` = 'download' where mediaType = 'download';");

        return 1;
    }

    protected function updateDoc($page)
    {
        $this->getConnection()->exec("UPDATE `activity` a ,`activity_doc` doc SET `finishData` = doc.finishDetail where a.mediaId = doc.id and a.`mediaType` = 'doc';");

        return 1;
    }

    protected function updateText($page)
    {
        $this->getConnection()->exec("UPDATE `activity` a ,`activity_text` atext SET a.`finishType`= 'time' , `finishData` = atext.finishDetail where a.mediaId = atext.id and a.`mediaType` = 'text';");

        return 1;
    }

    protected function updateAudio()
    {
        $this->getConnection()->exec("UPDATE `activity` SET `finishType` = 'end' where mediaType = 'audio';");
        
        return 1;
    }

    protected function updateVideo()
    {
        $this->getConnection()->exec("UPDATE `activity` a ,`activity_video` doc SET a.`finishData` = video.finishDetail ,a.`finishType` = video.finishType where a.mediaId = video.id and a.`mediaType` = 'video';");
        
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

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $column, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column})");
        }
    }

    protected function isJobExist($code)
    {
        $sql = "select * from biz_scheduler_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function makeUUID()
    {
        return sha1(uniqid(mt_rand(), true));
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
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



