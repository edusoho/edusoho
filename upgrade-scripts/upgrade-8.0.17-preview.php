<?php

use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\ArrayToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    const VERSION = '8.0.17';

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
            $this->logger(self::VERSION, 'error', $e->getMessage());
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
            return array(
                'index' => 1,
                'message' => '正在升级数据库',
            );
        } else {
            return $this->syncMissedChapters($index);
        }
    }

    private function syncMissedChapters($index)
    {
        $copiedCoursesSql = "SELECT id,parentId FROM `course_v8` WHERE parentId != 0";
        $copiedCourses = $this->getConnection()->fetchAll($copiedCoursesSql);
        if(empty($copiedCourses)) {
            return null;
        }
        $total = count($copiedCourses);
        $progress = ceil($index / ($total+2) * 100);
        $message = '正在升级数据库,当前进度:'.$progress.'%';

        if($index <= $total) {
            for($i = 0;$i < 5;$i++) {
                if (!isset($copiedCourses[$index-1])) {
                    continue;
                }
                $copiedCourse = $copiedCourses[$index-1];
                $this->checkAndUpdateChapter($copiedCourse);
                $this->logger('8.0.15', 'info', "更新课程#{$copiedCourse['id']}章节成功, 当前进度{$index}/{$total}.");
                ++$index;
            }
            return array(
                'index' => $index,
                'message' => $message,
            );
        } else {
            $this->logger(self::VERSION, 'info', "更新全部课程章节成功，升级结束");
            return null;
        }

    }

    private function checkAndUpdateChapter($copiedCourse)
    {
        if(empty($copiedCourse)) {
            return ;
        }

        $parentCourse = $this->getCourseDao()->get($copiedCourse['parentId']);

        if(empty($parentCourse)) {
            $this->logger(self::VERSION, 'notice', "原课程不存在");
            return ;
        }
        if(!empty($copiedCourse['id']) && !empty($copiedCourse['parentId'])){
            $parentCourseChapters = $this->getChapterDao()->findChaptersByCourseId($copiedCourse['parentId']);
            if(empty($parentCourseChapters)) {
                return ;
            }
            $parentCourseChapters = ArrayToolkit::index($this->sortChapters($parentCourseChapters),'id');
            $copiedCourseChapters = $this->getChapterDao()->findChaptersByCourseId($copiedCourse['id']);
            $copiedCourseChapters = !empty($copiedCourseChapters) ? $this->sortChapters($copiedCourseChapters) : array();

            $parentCourseChapterIds = ArrayToolkit::column($parentCourseChapters,'id');
            $copiedCourseChapterCopyIds = ArrayToolkit::column($copiedCourseChapters,'copyId');
            $unCopiedChapterIds = array_diff($parentCourseChapterIds, $copiedCourseChapterCopyIds);

            if (!empty($unCopiedChapterIds)) {
                $this->logger(self::VERSION, 'info', "存在差异的chapterIds:".json_encode($unCopiedChapterIds));
                $chapterMap = ArrayToolkit::index($copiedCourseChapters,'copyId');
                $copyFields = array(
                    'type',
                    'number',
                    'seq',
                    'title',
                );
                foreach ($unCopiedChapterIds as $unCopiedChapterId) {
                    if(isset($parentCourseChapters[$unCopiedChapterId])) {
                        $parentCourseChapter = $parentCourseChapters[$unCopiedChapterId];
                        $newChapter = ArrayToolkit::parts($parentCourseChapter,$copyFields);
                        $newChapter['courseId'] = $copiedCourse['id'];
                        $newChapter['copyId'] = $parentCourseChapter['id'];
                        if($parentCourseChapter['parentId'] > 0) {
                            $newChapter['parentId'] = $chapterMap[$parentCourseChapter['parentId']]['id'];
                        }
                        $newChapter = $this->getChapterDao()->create($newChapter);
                        $chapterMap[$parentCourseChapter['id']] = $newChapter;

                    }
                }
            }
        }



    }

    private function sortChapters($chapters)
    {
        usort($chapters, function ($a, $b) {
            if ($a['parentId'] < $b['parentId']) {
                return -1;
            }

            if ($a['parentId'] == $b['parentId']) {
                return $a['id'] > $b['id'];
            }

            return 1;
        });
        return $chapters;
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
