<?php

use Symfony\Component\Filesystem\Filesystem;
use Biz\Util\PluginUtil;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\BlockToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    private $pageSize = 1000;

    public function __construct($biz)
    {
        parent::__construct($biz);
    }

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $systemUser = $this->getConnection()->fetchAssoc("select * from user where type='system';");
            $this->systemUserId = empty($systemUser['id']) ? 0 : $systemUser['id'];

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
        $this->getSettingService()->set("crontab_next_executed_time", time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'addCourseChapters',
            'updateTaskFields',
            'updateChpaterCopyId'
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key+1] = $funcName;
        }

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

        $index = $this->generateIndex($step, $page);
        if ($step <= count($funcNames)) {
            return array(
                'index' => $index,
                'message' => '升级数据...',
                'progress' => 0
            );
        }
    }

    protected function addCourseChapters()
    {
        $this->addSourceTaskId('course_chapter');
        $connection = $this->getConnection();
        $connection->exec("DELETE FROM `course_chapter` WHERE `migrate_task_id`>0");
        $connection->exec("
            INSERT into `course_chapter` (
                `courseId`,
                `type`,
                `number`,
                `seq`,
                `title`,
                `createdTime`,
                `copyId`,
                `migrate_task_id`
            )
            select 
                `courseId` as `courseId`,
                'lesson' as `type`,
                case when `number`='' then 0 else `number` end as `number`,
                `seq` as `seq`,
                `title` as `title`,
                `createdTime` as `createdTime`,
                0 as `copyId`,
                `id` as `migrate_task_id`
            from `course_task` where courseId in (select id from course_v8 where courseType='normal');
        ");
        return 1;
    }

    protected function updateTaskFields()
    {
        $connection = $this->getConnection();
        $connection->exec("
            UPDATE `course_task` as ct,`course_chapter` as cc set ct.mode='lesson',ct.categoryId=cc.id WHERE cc.migrate_task_id=ct.id
        ");
        return 1;
    }

    protected function updateChpaterCopyId()
    {
        $connection = $this->getConnection();
        $connection->exec("
            update course_chapter cc1,course_chapter cc2 set cc1.copyId=cc2.id where cc1.migrate_task_id>0 and cc2.migrate_task_id =(select copyId from course_task ct where ct.id=cc1.migrate_task_id and copyId>0)
        ");
        return 1;
    }

    protected function addSourceTaskId($table)
    {
        $connection = $this->getConnection();
        if (!$this->isFieldExist($table, 'migrate_task_id')) {
            $connection->exec("ALTER TABLE `{$table}` ADD COLUMN `migrate_task_id` int(10) NOT NULL DEFAULT '0' COMMENT '来源任务表id';");
        }

        if (!$this->isIndexExist($table, 'migrate_task_id', 'migrate_task_id')) {
            $connection->exec("ALTER TABLE `{$table}` ADD INDEX migrate_task_id (migrate_task_id);");
        }
    }

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger( 'info', '删除缓存');
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

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
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