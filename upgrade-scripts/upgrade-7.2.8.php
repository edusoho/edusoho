<?php

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->batchUpdate($index);
            $this->getConnection()->commit();

            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting          = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System.SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System.SettingService')->set("crontab_next_executed_time", time());
    }

    private function updateScheme()
    {
        $connection = $this->getConnection();
        if (!$this->isFieldExist('course_lesson_replay', 'copyId')) {
            $connection->exec("ALTER TABLE course_lesson_replay ADD `copyId` int(10) DEFAULT '0' COMMENT '复制回放的ID';");
        }

        if ($this->isFieldExist('course_lesson', 'suggestHours')) {
            $connection->exec("ALTER TABLE `course_lesson` DROP `suggestHours`");
        }

        if ($this->isFieldExist('open_course_lesson', 'suggestHours')) {
            $connection->exec("ALTER TABLE `open_course_lesson` DROP `suggestHours`");
        }

        if (!$this->isFieldExist('course', 'buyExpireTime')) {
            $connection->exec("ALTER TABLE `course` ADD `buyExpireTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '购买开放有效期' AFTER `buyable`");
        }

        if (!$this->isFieldExist('course_review', 'meta')) {
            $connection->exec("ALTER TABLE course_review add `meta` text  COMMENT '评价元信息'");
        }

        if (!$this->isFieldExist('classroom_review', 'meta')) {
            $connection->exec("ALTER TABLE classroom_review add `meta` text  COMMENT '评价元信息'");
        }
    }

    protected function batchUpdate($index)
    {
        if ($index === 0) {
            $this->updateScheme();
            return array(
                'index'    => 1,
                'message'  => '正在升级数据...',
                'progress' => 0
            );
        }

        $conditions = array('type' => 'live', 'copyId' => 0);
        $total      = $this->getCourseService()->searchLessonCount($conditions);
        $maxPage    = ceil($total / 100) ? ceil($total / 100) : 1;

        $this->syncLiveReplay();

        if ($index <= $maxPage) {
            return array(
                'index'    => $index + 1,
                'message'  => '正在升级数据...',
                'progress' => 0
            );
        }
    }

    private function syncLiveReplay()
    {
        $connection = $this->getConnection();

        $conditions = array('type' => 'live', 'copyId' => 0);
        $results    = $this->getCourseService()->searchLessons($conditions, array('createdTime', 'DESC'), 0, 100);

        if (!$results) {
            return false;
        }

        foreach ($results as $key => $lesson) {
            if ($lesson['replayStatus'] != 'generated') {
                continue;
            }

            $replaySql     = "select id,replayId from course_lesson_replay where lessonId={$lesson['id']}";
            $lessonReplays = $connection->fetchAll($replaySql);

            if (!$lessonReplays) {
                continue;
            }

            $sql         = "select id,courseId,copyId from course_lesson where copyId={$lesson['id']}";
            $copyLessons = $connection->fetchAll($sql);

            if (!$copyLessons) {
                continue;
            }

            $copyLessonIds = ArrayToolkit::column($copyLessons, 'id');
            $copyLessonIds = implode(',', $copyLessonIds);

            foreach ($lessonReplays as $replay) {
                $sql = "update course_lesson_replay set copyId = {$replay['id']} where lessonId in($copyLessonIds) and replayId='".$replay['replayId']."'";

                $connection->exec($sql);
            }
        }

        return true;
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql    = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql    = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql    = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isCrontabJobExist($code)
    {
        $sql    = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * @return \Topxia\Service\System\Impl\SettingServiceImpl
     */
    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    private function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    /**
     * @return \Permission\Service\Role\Impl\RoleServiceImpl
     */
    private function getRoleService()
    {
        return ServiceKernel::instance()->createService('Permission:Role.RoleService');
    }
}

abstract class AbstractUpdater
{
    protected $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return \Topxia\Service\Common\Connection
     */
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
