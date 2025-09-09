<?php

use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme();
            $this->getConnection()->commit();
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

    private function updateScheme()
    {
        $this->getConnection()->exec("ALTER TABLE org MODIFY  `seq` int(11) NOT NULL DEFAULT '0' COMMENT '索引';");

        $sql = "select count(source.id) from activity dist, activity source where dist.mediaType = 'live' and source.mediaType = 'live' and source.mediaId != dist.mediaId and dist.copyId = source.id and source.copyId = 0;";
        $count = $this->getConnection()->fetchColumn($sql);
        if (!empty($count)) {
            $sql = "UPDATE activity source_activity, activity dist_activity SET dist_activity.mediaId = source_activity.mediaId WHERE dist_activity.mediaType = 'live' AND source_activity.mediaType = 'live' AND dist_activity.copyId = source_activity.id;";
            $this->getConnection()->exec($sql);
        }
        //get original data
        $sql = "select  ck.* from course_task  ck, course_v8 c  where ck.courseid = c.id and c.parentid =0 and ck.type = 'download' and ck.migrateLessonId  > 0";
        $courseTasks = $this->getConnection()->fetchAll($sql);
        if (empty($courseTasks)) {
            return false;
        }
        $this->logger('8.0.10', 'info', 'total count  :' . count($courseTasks) );
        foreach ($courseTasks as $courseTask) {
            $this->logger('8.0.10', 'info', 'original course Task   :' .json_encode($courseTask)  );
            $sql = "select * from course_task where type = 'download' and  id != ? and  title = ? and migrateLessonId > ? ";
            $copyCourseTasks = $this->getConnection()->fetchAll($sql, array($courseTask['id'], $courseTask['title'], 0));

            $sql = "select * from course_v8 where parentId = ?";
            $copyCourses = $this->getConnection()->fetchAll($sql, array($courseTask['courseId']));
            $copyCourses = \AppBundle\Common\ArrayToolkit::index($copyCourses, 'id');
            if(empty($copyCourseTasks)){
                $this->logger('8.0.10', 'info', 'copy course Task deal   is empty' );
            }
            foreach ($copyCourseTasks as $copyCourseTask) {
                if (!empty($copyCourses[$copyCourseTask['courseId']])) {
                    $this->logger('8.0.10', 'info', 'copy course Task deal  :' .json_encode($copyCourseTask)  );
                    $this->getConnection()->update('course_task', array('copyId' => $courseTask['id']), array('id' => $copyCourseTask['id']));
                    $this->getConnection()->update('activity', array('copyId' => $courseTask['activityId']), array('id' => $copyCourseTask['activityId']));
                }
            }
        }

        //删除已删除任务对应的章节
        $sql = "DELETE FROM course_chapter WHERE type = 'lesson' AND id NOT IN (SELECT categoryId FROM course_task WHERE categoryId > 0)";
        $this->getConnection()->exec($sql);
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

    protected function logger($version, $level, $message)
    {
        $data = date('Y-m-d H:i:s')." [{$level}] {$version} ".$message.PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    protected function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'] . '/../app/logs/upgrade.log';
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
