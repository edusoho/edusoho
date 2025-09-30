<?php

use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme();
            $this->getConnection()->commit();
            if (!empty($result)) {
                return $result;
            }
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

    private function insertVideoActivity()
    {
        ;
    }

    private function updateScheme()
    {
        $sql = "
        insert into `activity_video` (
            `mediaSource`,
            `mediaId`,
            `mediaUri`,
            `finishType`,
            `finishDetail`,
            `migrateLessonId`
            )
            select
            `mediaSource`,
            `mediaId`,
            `mediaUri`,
            case when `mediaSource` = 'self' then 'end' else 'time' end,
            case when `mediaSource` = 'self' then 0 else CEIL(`length`/60) end,
            `id`
            from `course_lesson` where  type ='video' 
            and id not in (select migrateLessonId from activity_video)
            and id in (select migrateLessonId from activity where  type ='video');
        ";
        $this->getConnection()->exec($sql);

        $sql = "
        update  activity_video ao, course_lesson cn 
            set ao.mediaSource = cn.mediaSource,
            ao.mediaId =  cn.mediaId
            where ao.migrateLessonId = cn.id  and  ao.mediaSource = '' and ao.mediaId=0
        ";
        $this->getConnection()->exec($sql);

        $sql = "
            UPDATE  `activity` AS ay ,`activity_video` AS vy SET ay.`mediaId`  =  vy.`id`
               WHERE ay.`migrateLessonId`  = vy.`migrateLessonId`   AND ay.`mediaType` = 'video'  and vy.`migrateLessonId` >0;
        ";
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
