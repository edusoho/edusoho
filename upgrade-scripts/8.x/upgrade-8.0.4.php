<?php

use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme($index);
            $this->testpaperUpdate();
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath($this->biz['kernel.root_dir']. "/../web/install");
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
        $this->getConnection()->exec("update `course_set_v8` cs , `course_v8` c set  cs.`defaultCourseId` = c.id where   c.`courseSetid` = cs.id  and cs.`defaultCourseId`= 0 ");

        $countSql = "SELECT count(id) from testpaper_result_v8 where migrateResultId > 0 and courseId = 0 AND type = 'testpaper';";
        $count = $this->getConnection()->fetchColumn($countSql);

        $maxPage = ceil($count / 100) ? ceil($count / 100) : 1;
        $page = 10000;
        $start = ($index)*$page;

        if (!empty($count)) {
            $sql = "SELECT id FROM testpaper_result_v8 WHERE migrateResultId > 0 and type = 'testpaper' order by id asc limit 1 offset {$start}";
            $startId = $this->getConnection()->fetchColumn($sql);

            if (empty($startId)) {
                return ;
            }

            $end = $start + $page;
            $sql = "SELECT id FROM testpaper_result_v8 WHERE migrateResultId > 0 and type = 'testpaper' order by id asc limit 1 offset {$end}";
            $endId = $this->getConnection()->fetchColumn($sql);
            $endWhere = empty($endId) ? '' : " and t.id < {$endId} ";

            $sql = "UPDATE testpaper_result_v8 as t, course_lesson as cl set 
                t.courseId = cl.courseId,
                t.courseSetId = cl.courseId, 
                t.lessonId = cl.id 
                where cl.type = 'testpaper' and t.type = 'testpaper' and t.testId = cl.mediaId and t.courseId = 0 and migrateResultId > 0 and t.id >= {$startId} {$endWhere} ";
            $this->getConnection()->exec($sql);

        }

        if ($index <= $maxPage) {
            return array(
                'index'    => $index + 1,
                'message'  => '正在升级数据...',
                'progress' => 0
            );
        }
    }

    private function testpaperUpdate()
    {
        $countSql = "SELECT count(id) from testpaper where passedScore > 0";
        $count = $this->getConnection()->fetchColumn($countSql);

        if (empty($count)) {
            return;
        }

        $sql = "SELECT *,t.passedScore FROM testpaper_v8 as tv LEFT JOIN testpaper AS t on tv.migrateTestId = t.id WHERE tv.type = 'testpaper' and t.passedScore > 0 AND tv.migrateTestId > 0";
        $results = $this->getConnection()->fetchAll($sql);

        foreach($results as $result) {
            $passedCondition = json_encode(array($result['passedScore']));
            $sql = "UPDATE testpaper_v8 set passedCondition = '{$passedCondition}' where id={$result['id']}";
            $this->getConnection()->exec($sql);
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
