<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;


class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();

        try {
            $result = $this->updateVideoLesson($index);
            $this->getConnection()->commit();

            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir        = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."/../web/install");
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


    private function updateVideoLesson($index)
    {
        $totalCounts = $this->countVideoLessons();
        $maxPage = ceil($totalCounts / 100) ? ceil($totalCounts / 100) : 1;
        $page = 100;
        $start = ($index)*$page;

        $conditions = array(
            'mediaSource' => 'youku'
        );
        $lessons = $this->getLessonDao()->searchLessons(
            $conditions,
            array('id', 'desc'),
            $start,
            100
        );

        foreach ($lessons as $lesson) {
            if ($lesson['mediaSource'] == 'youku') {
                if (!empty($lesson['mediaUri'])) {
                    $correctUri = str_replace('http:', '', $lesson['mediaUri']);
                    $correctUri = str_replace('https:', '', $correctUri);
                    $fields['mediaSource'] = $correctUri;
                    $this->getLessonDao()->updateLesson($lesson['id'], $fields);
                }
            }
        }

        if ($index <= $maxPage) {
            return array(
                'index'    => $index + 1,
                'message'  => '正在升级数据...',
                'progress' => 0
            );
        }
    }

    private function countVideoLessons()
    {
        $conditions = array(
            'mediaSource' => 'youku'
        );

        return $this->getLessonDao()->searchLessonCount($conditions);
    }

    protected function getLessonDao()
    {
        return ServiceKernel::instance()->createDao('course.lessonDao');
    }
}

abstract class AbstractUpdater
{
    protected $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

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
