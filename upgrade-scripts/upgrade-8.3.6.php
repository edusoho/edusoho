<?php

use Symfony\Component\Filesystem\Filesystem;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\CloudAPIFactory;
use QiQiuYun\SDK\Auth;
use QiQiuYun\SDK\HttpClient\Client;

class EduSohoUpgrade extends AbstractUpdater
{
    private $pageSize = 3000;


    public function __construct($biz)
    {
        parent::__construct($biz);
        $this->setCourseUpdateHelper();
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
        $definedFuncNames = array(
            'updateClassroomCopyCourseEnableAudio',
            'addActivityColumn',
            'updateHomeworkAndExercise',
            'updatePpt',
            'updateLive',
            'updateDownload',
            'updateDoc',
            'updateText',
            'updateAudio',
            'updateVideo',
            'updateFlash',
            'updateDiscuss',
            'updateTestPaper',
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

    protected function updateDiscuss() 
    {
        $this->getConnection()->exec("UPDATE `activity` SET `finishType` = 'join' where mediaType = 'discuss';");

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
        $this->getConnection()->exec("UPDATE `activity` a ,`activity_doc` doc SET a.`finishType`= 'time', `finishData` = doc.finishDetail where a.mediaId = doc.id and a.`mediaType` = 'doc';");

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
        $this->getConnection()->exec("UPDATE `activity` a ,`activity_video` video SET a.`finishData` = video.finishDetail ,a.`finishType` = video.finishType where a.mediaId = video.id and a.`mediaType` = 'video';");
        
        return 1;
    }

    protected function updateTestPaper($page)
    {
        $helper = new BatchUpdateHelper($this->getActivityDao());
        $start = $this->getStart($page);
        $sql = "SELECT * FROM `activity` WHERE `mediaType` = 'testpaper' limit {$start}, 3000";
        $activities = $this->getConnection()->fetchAll($sql);
        if (empty($activities)) {
            return 1;
        }

        $testpaperActivityIds = ArrayToolkit::column($activities, 'mediaId');
        $testpaperActivityIds = implode(',', $testpaperActivityIds);
        $testpaperActivities = $this->getConnection()->fetchAll("SELECT * FROM `activity_testpaper` where id in ({$testpaperActivityIds})");
        if (empty($testpaperActivities)) {
            return $page + 1;
        }

        $testpaperActivities = ArrayToolkit::index($testpaperActivities, 'id');

        $testpaperIds = ArrayToolkit::column($testpaperActivities, 'mediaId');
        $testpaperIds = implode(',', $testpaperIds);

        $testpapers = $this->getConnection()->fetchAll("SELECT * FROM `testpaper_v8` where id in ({$testpaperIds})");

        if (empty($testpapers)) {
            return $page + 1;
        }
        $testpapers = ArrayToolkit::index($testpapers, 'id');

        foreach ($activities as $key => $value) {
            if (empty($testpaperActivities[$value['mediaId']])) {
                continue;
            }
            $testpaperActivity = $testpaperActivities[$value['mediaId']];
  
            if (empty($testpapers[$testpaperActivity['mediaId']])) {
                continue;
            }
 
            $testpaper = $testpapers[$testpaperActivity['mediaId']];
            $finishCondition = json_decode($testpaperActivity['finishCondition'], true);

            $param = array(
                'finishType' => empty($finishCondition['type']) ? 'submit' : $finishCondition['type'],
                'finishData' => empty($finishCondition['finishScore']) ? 0 : round($finishCondition['finishScore'] / $testpaper['score'], 5),
            );
    
            $helper->add('id', $value['id'], $param);
        }
        $helper->flush();

        return $page + 1;
    }

    protected function updateClassroomCopyCourseEnableAudio(){
        $sql = "SELECT a.id,a.parentId,a.enableAudio as a_enableAudio,b.enableAudio as b_enableAudio from course_v8 as a LEFT JOIN course_v8 as b ON a.parentId = b.id where a.locked = 1 and a.enableAudio <> b.enableAudio ";
        $courses = $this->getConnection()->fetchAll($sql);

        foreach ($courses as $course){
            $this->courseUpdateHelper->add('id', $course['id'], array('enableAudio' => $course['b_enableAudio']));
        }

        $this->courseUpdateHelper->flush();
        
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

    protected function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
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

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger('info', '删除缓存');

        return 1;
    }

    protected function getStart($page)
    {
        return ($page - 1) * $this->pageSize;
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

    private function setCourseUpdateHelper()
    {
        $courseDao = $this->createDao('Course:CourseDao');
        $this->courseUpdateHelper = new BatchUpdateHelper($courseDao);

        return $this->courseUpdateHelper;
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
