<?php

use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme($index);
            $this->insertVideoActivity();
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
            from `course_lesson` where  type ='video' and id not in (select migrateLessonId from activity_video);
        ";
        $this->getConnection()->exec($sql);
    }

    private function updateScheme($index)
    {

        $countSql = "select count(id) from `activity`  where `mediaType` = 'video' and mediaid = 0";
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return false;
        }
        $this->perPageCount = 20000;
        $maxPage = ceil($count / $this->perPageCount) ? ceil($count / $this->perPageCount) : 1;
        $start = $index * $this->perPageCount;

        $this->getConnection()->exec(
            "
        UPDATE  `activity` AS ay,
            (select * from  `activity_video` AS vy  order by id limit {$start}, {$this->perPageCount})AS vy 
        SET ay.`mediaId`  =  vy.`id`       
        where  ay.`migrateLessonId`  = vy.`migrateLessonId`   AND ay.`mediaType` = 'video' and vy.`migrateLessonId` >0
        "
        );

        if ($index <= $maxPage) {
            return array(
                'index' => $index + 1,
                'message' => '正在升级数据...',
                'progress' => 0
            );
        }
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
