<?php

use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class EdusohoUpgrade extends AbstractUpdater
{
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
    }

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        
        $filesystem = new Filesystem();
        $deleteCachePath = dirname($cachePath);
        $filesystem->remove($deleteCachePath);

        clearstatcache(true);

        $this->logger( 'info', '删除缓存');
        return 1;
    }

    private function updateScheme($index)
    {
        $funcNames = array(
            1 => 'deleteCache',
            2 => 'syncVideoMediaId',
            3 => 'syncAudioMediaId',
            4 => 'syncDocMediaId',
            5 => 'syncPptMediaId',
            6 => 'syncFlashMediaId',
        );

        if ($index == 0) {
            $this->logger( 'info', '开始执行升级脚本');
            $this->deleteCache();

            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '升级数据...',
                'progress' => 0
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $funcNames[$step];
        $page = $this->$method($page);

        if ($page == 1) {
            $step++;
        }

        if ($step <= count($funcNames)) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '升级数据...',
                'progress' => 0
            );
        }
    }

    public function syncVideoMediaId()
    {
        $sql = "update `course_v8` a 
                inner join `activity` b on a.id = b.fromCourseId 
                inner join `activity` c on b.copyId = c.id 
                inner join `activity_video` d on b.mediaId = d.id 
                inner join `activity_video` e on c.mediaId = e.id 
                set d.mediaId = e.mediaId 
                where d.mediaId != e.mediaId and a.locked = 1 and a.parentId > 0 and c.id > 0 and e.id > 0 and b.mediaType = 'video';";
        $this->getConnection()->exec($sql);
        return 1;

    }

    public function syncAudioMediaId()
    {
        $sql = "update `course_v8` a 
                inner join `activity` b on a.id = b.fromCourseId 
                inner join `activity` c on b.copyId = c.id 
                inner join `activity_audio` d on b.mediaId = d.id 
                inner join `activity_audio` e on c.mediaId = e.id 
                set d.mediaId = e.mediaId 
                where d.mediaId != e.mediaId and a.locked = 1 and a.parentId > 0 and c.id > 0 and e.id > 0 and b.mediaType = 'audio';";
        $this->getConnection()->exec($sql);
        return 1;
    }

    public function syncDocMediaId()
    {
        $sql = "update `course_v8` a 
                inner join `activity` b on a.id = b.fromCourseId 
                inner join `activity` c on b.copyId = c.id 
                inner join `activity_doc` d on b.mediaId = d.id 
                inner join `activity_doc` e on c.mediaId = e.id 
                set d.mediaId = e.mediaId 
                where d.mediaId != e.mediaId and a.locked = 1 and a.parentId > 0 and c.id > 0 and e.id > 0 and b.mediaType = 'doc';";
        $this->getConnection()->exec($sql);
        return 1;
    }

    public function syncPptMediaId()
    {
        $sql = "update `course_v8` a 
                inner join `activity` b on a.id = b.fromCourseId 
                inner join `activity` c on b.copyId = c.id 
                inner join `activity_ppt` d on b.mediaId = d.id 
                inner join `activity_ppt` e on c.mediaId = e.id 
                set d.mediaId = e.mediaId 
                where d.mediaId != e.mediaId and  a.locked = 1 and a.parentId > 0 and c.id > 0 and e.id > 0 and b.mediaType = 'ppt';";
        $this->getConnection()->exec($sql);
        return 1;
    }

    public function syncFlashMediaId()
    {
        $sql = "update `course_v8` a 
                inner join `activity` b on a.id = b.fromCourseId 
                inner join `activity` c on b.copyId = c.id 
                inner join `activity_flash` d on b.mediaId = d.id 
                inner join `activity_flash` e on c.mediaId = e.id 
                set d.mediaId = e.mediaId 
                where d.mediaId != e.mediaId and  a.locked = 1 and a.parentId > 0 and c.id > 0 and e.id > 0 and b.mediaType = 'flash';";
        $this->getConnection()->exec($sql);
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

    private function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
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

    /**
     * @param  $type
     *
     * @return Activity
     */
    private function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
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
}