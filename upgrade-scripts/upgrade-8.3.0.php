<?php

use Symfony\Component\Filesystem\Filesystem;
use Biz\Util\PluginUtil;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\BlockToolkit;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class EduSohoUpgrade extends AbstractUpdater
{
    private $pageSize = 1000;

    public function __construct($biz)
    {
        parent::__construct($biz);
        $this->setCourseUpdateHelper();
        $this->setCourseSetUpdateHelper();
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
            'addNewFields',
            'addCourseChapters',
            'updateTaskFields',
            'updateChpaterCopyId',
            'updateLessonNum',
            'updateLessonIsOptionAndStatus',
            'updateCourseShowServices',
            'updateCourseIsFree',
            'updateCourseTitle',
            'updateCourseSetSummary'
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

    protected function addNewFields()
    {
        $connection = $this->getConnection();

        if (!$this->isFieldExist('course_v8', 'isShowUnpublish')) {
            $connection->exec("
                ALTER TABLE `course_v8` ADD `isShowUnpublish` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '学员端是否展示未发布课时';
            ");
        }

        if (!$this->isFieldExist('course_v8', 'seq')) {
            $connection->exec("
                ALTER TABLE `course_v8` ADD COLUMN `seq` int(10)  NOT NULL DEFAULT 0 COMMENT '排序序号' AFTER `status`;
            ");
        }

        if (!$this->isFieldExist('course_v8', 'lessonNum')) {
            $connection->exec("
                ALTER TABLE `course_v8` ADD COLUMN `lessonNum` int(10)  NOT NULL DEFAULT 0 COMMENT '课时总数' AFTER `compulsoryTaskNum`;
            ");
        }

        if (!$this->isFieldExist('course_v8', 'publishLessonNum')) {
            $connection->exec("
                ALTER TABLE `course_v8` ADD COLUMN `publishLessonNum` int(10)  NOT NULL DEFAULT 0 COMMENT '课时发布数量' AFTER `lessonNum`;
            ");
        }

        if (!$this->isFieldExist('course_chapter', 'status')) {
            $connection->exec("
                ALTER TABLE `course_chapter` ADD `status` varchar(20) NOT NULL DEFAULT 'published' COMMENT '发布状态 create|published|unpublished' AFTER `copyId`;
            ");
        }

        if (!$this->isFieldExist('course_chapter', 'isOptional')) {
            $connection->exec("
                ALTER TABLE `course_chapter` add `isOptional` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否选修' AFTER `status`;
            ");
        }

        if (!$this->isFieldExist('course_chapter', 'migrate_task_id')) {
            $connection->exec("ALTER TABLE `course_chapter` ADD COLUMN `migrate_task_id` int(10) NOT NULL DEFAULT '0' COMMENT '来源任务表id';");
        }

        if (!$this->isIndexExist('course_chapter', 'migrate_task_id', 'migrate_task_id')) {
            $connection->exec("ALTER TABLE `course_chapter` ADD INDEX migrate_task_id (migrate_task_id);");
        }

        if (!$this->isFieldExist('course_v8', 'subtitle')) {
            $connection->exec("
                ALTER TABLE `course_v8` ADD `subtitle` varchar(120) DEFAULT '' COMMENT '计划副标题' AFTER `title`;
            ");
        }

        return 1;
    }

    protected function addCourseChapters($page)
    {
        $connection = $this->getConnection();
        $countSql = "SELECT count(*) from `course_task` where courseId in (select id from course_v8 where courseType='normal')";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return 1;
        }
        $start = $this->getStart($page);
        if ($page == 1) {
            $connection->exec("DELETE FROM `course_chapter` WHERE `migrate_task_id`>0");
        }
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
            from `course_task` where courseId in (select id from course_v8 where courseType='normal') order by id limit {$start}, {$this->pageSize};
        ");
        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return 1;
        }

        return $nextPage;
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
            update course_chapter cc1,course_chapter cc2 set cc1.copyId=cc2.id where cc1.migrate_task_id>0 and cc2.migrate_task_id =(select ct.copyId from course_task ct where ct.id=cc1.migrate_task_id and ct.copyId>0)
        ");
        return 1;
    }

    protected function updateLessonNum()
    {
        $connection = $this->getConnection();
        $connection->exec("
            update course_v8 cv,(SELECT courseid,count(*) num FROM `course_chapter` WHERE type='lesson' group by courseid) cc set cv.lessonNum=cc.num where cv.id=cc.courseid
        ");
        $connection->exec("
            update course_v8 cv,(SELECT courseid,count(*) num FROM `course_chapter` WHERE type='lesson' and status='published' group by courseid) cc set cv.publishLessonNum=cc.num where cv.id=cc.courseid
        ");
        return 1;
    }


    protected function updateLessonIsOptionAndStatus()
    {
        $connection = $this->getConnection();
        $connection->exec("
            UPDATE `course_chapter` cc,course_task ct SET cc.isoptional = ct.isoptional,cc.status = ct.status where cc.id=ct.categoryId and cc.type='lesson' and ct.mode='lesson'
        ");

        return 1;      
    }

    protected function updateCourseShowServices()
    {
        $connection = $this->getConnection();
        if ($this->isFieldExist('course_v8', 'showServices')) {
            $connection->exec("
                ALTER TABLE course_v8 alter column `showServices` set default 0
            ");
        }
        $connection->exec("
            UPDATE course_v8 SET showServices = 0 where services = '' or services is null
        ");

        $connection->exec("
            UPDATE course_v8 SET showServices = 1 where services != '' and services is not null
        ");

        return 1;     
    }

    protected function updateCourseIsFree()
    {
        $connection = $this->getConnection();
        $connection->exec("
            UPDATE course_v8 SET isFree = 0 where isFree = 1 and originPrice > 0
        ");

        return 1;
    }

    protected function updateCourseTitle($page)
    {
        $connection = $this->getConnection();
        $countSql = "SELECT count(*) from course_v8";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return 1;
        }
        $start = $this->getStart($page);
        $sql = "select id from `course_v8` where isDefault = 1 and courseSetId in (SELECT courseSetId FROM course_v8 group by courseSetId HAVING count(courseSetId)=1 and isDefault = 1) limit {$start}, {$this->pageSize}";
        $courses = $this->getConnection()->fetchAll($sql);
        foreach ($courses as $course) {
             $this->courseUpdateHelper->add('id', $course['id'], array('title' => ''));
        }
        $this->courseUpdateHelper->flush();
        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return 1;
        }

        return $nextPage;
    }

    protected function updateCourseSetSummary($page)
    {
        $connection = $this->getConnection();
        $countSql = "SELECT count(*) from course_v8";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return 1;
        }
        $start = $this->getStart($page);
        $sql = "select id, summary, courseSetId from course_v8 where isDefault = 1 and courseSetId in (SELECT courseSetId FROM course_v8 group by courseSetId HAVING count(courseSetId)=1 and isDefault=1) limit {$start}, {$this->pageSize}";
        $courses = $this->getConnection()->fetchAll($sql);
        foreach ($courses as $course) {
             if (!empty($course['summary'])) {
                $this->courseSetUpdateHelper->add('id', $course['courseSetId'], array('summary' => $course['summary']));
             }
        }
        $this->courseSetUpdateHelper->flush();
        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return 1;
        }

        return $nextPage;
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

    protected function getLastPage($count)
    {
        return ceil($count / $this->pageSize);
    }

    protected function getNextPage($count, $currentPage)
    {
        $diff = $this->getLastPage($count) - $currentPage;
        return $diff > 0 ? $currentPage + 1 : 0;
    }

    protected function getStart($page)
    {
        return ($page - 1) * $this->pageSize;
    }

    private function setCourseUpdateHelper()
    {
        $courseDao = $this->createDao('Course:CourseDao');
        $this->courseUpdateHelper = new BatchUpdateHelper($courseDao);

        return $this->courseUpdateHelper;
    }

    private function setCourseSetUpdateHelper()
    {
        $courseSetDao = $this->createDao('Course:CourseSetDao');
        $this->courseSetUpdateHelper = new BatchUpdateHelper($courseSetDao);

        return $this->courseSetUpdateHelper;
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