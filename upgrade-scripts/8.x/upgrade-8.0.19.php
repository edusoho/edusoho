<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\ArrayToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    const VERSION = '8.0.19';

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $time = time() + 240;
            $lockFile = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade.lock';
            file_put_contents($lockFile, (string) $time, LOCK_EX);
            $result = $this->batchUpdate($index);
            $this->getConnection()->commit();
            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            $this->logger(self::VERSION, 'error', $e->getTraceAsString());
            throw $e;
        }

        try {
            $dir = realpath($this->biz['kernel.root_dir']. "/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
            $this->logger(self::VERSION, 'error', $e->getMessage());
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set("crontab_next_executed_time", time());
    }

    private function batchUpdate($index)
    {
        if ($index == 0) {
            $this->deleteCache();
            $this->otherMigrations();
            return array(
                'index' => 1,
                'message' => '正在升级数据库',
            );
        } else {
            return $this->syncMissedChapters($index);
        }
    }

    private function otherMigrations()
    {
        if (!$this->isIndexExist('cash_orders','sn','sn')) {
            $this->getConnection("ALTER TABLE `cash_orders` ADD UNIQUE( `sn`)");
        }

        if (!$this->isTableExist('course_chapter_8_0_19_backup')) {
            $sql = "CREATE TABLE course_chapter_8_0_19_backup (id INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT) select * from course_chapter";
            $this->getConnection()->exec($sql);

            $this->logger(self::VERSION, 'info', '备份`course_chapter`表成功');
        }
        
    }

    private function syncMissedChapters($index)
    {
        $copiedCoursesSql = "SELECT id,parentId FROM `course_v8` WHERE parentId > 0 and locked = 1";
        $copiedCourses = $this->getConnection()->fetchAll($copiedCoursesSql);
        if(empty($copiedCourses)) {
            $this->logger(self::VERSION, 'info', "没有班级复制课程，无需更新章节");
        }

        $courseIds = ArrayToolkit::column($copiedCourses,'parentId');
        if(empty($courseIds)) {
            return null;
        }
        $sql = "SELECT count(id) FROM course_chapter WHERE courseId in(".implode(',', $courseIds).")";
        $count = $this->getConnection()->fetchColumn($sql);

        $pageSize = 100;
        $maxPage = ceil($count / $pageSize);
        $start = ($index - 1) * $pageSize;

        $sql = "SELECT id,courseId,type,parentId,number,seq,title,copyId FROM course_chapter WHERE courseId in(".implode(',', $courseIds).") ORDER BY parentId ASC LIMIT {$start}, {$pageSize}";
        $chapters = $this->getConnection()->fetchAll($sql);

        $copyCourseIds = ArrayToolkit::column($copiedCourses, 'id');

        if(empty($copyCourseIds)) {
            return null;
        }
        $sql = "SELECT id,courseId,parentId,copyId FROM course_chapter WHERE courseId in(".implode(',', $copyCourseIds).")";
        $copyChapters = $this->getConnection()->fetchAll($sql);
        $copyChapters = ArrayToolkit::group($copyChapters, 'courseId');

        $newChapters = array();
        foreach ($chapters as $chapter) {
            foreach ($copiedCourses as $copiedCourse) {
                $copyCourseChapters = empty($copyChapters[$copiedCourse['id']]) ? array() : $copyChapters[$copiedCourse['id']];
                $copyCourseChapters = ArrayToolkit::index($copyCourseChapters, 'copyId');

                if ($chapter['courseId'] != $copiedCourse['parentId'] || !empty($copyCourseChapters[$chapter['id']])) {
                    continue;
                }

                $newChapter = $chapter;
                unset($newChapter['id']);

                $newChapter['courseId'] = $copiedCourse['id'];
                $newChapter['copyId'] = $chapter['id'];

                if ($chapter['parentId'] > 0) {
                    $newChapter['parentId'] = empty($copyCourseChapters[$chapter['parentId']]) ? 0 : $copyCourseChapters[$chapter['parentId']]['id'];
                }

                $newChapters[] = $newChapter;
            }
        }

        $this->getChapterDao()->batchCreate($newChapters);

        $this->logger(self::VERSION, 'info', "更新班级复制课程章节(影响：".count($newChapters).")(page:{$index})");

        if ($index < $maxPage) {
            $index++;
            return array(
                'index' => $index,
                'message' => "正在升级数据...",
            );
        }
        
        return null;
    }

    private function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove(dirname($cachePath));
        clearstatcache(true);
        sleep(3);
        //注解需要该目录存在
        if (!$filesystem->exists($cachePath.'/annotations/topxia')) {
            $filesystem->mkdir($cachePath.'/annotations/topxia');
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
        $sql    = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isCrontabJobExist($code)
    {
        $sql = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
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
        return $this->biz['kernel.root_dir'].'/../app/logs/upgrade.log';
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function getChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }

    private function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }
}

abstract class AbstractUpdater
{
    protected $biz;
    protected $kernel;

    public function __construct($biz)
    {
        $this->biz = $biz;
        $this->kernel = ServiceKernel::instance();
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
