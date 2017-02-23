<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Common\ArrayToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    private static $pageNum = 1000;

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try{
            $result = $this->batchUpdate($index);
            $this->getConnection()->commit();
            if (!empty($result)) {
                return $result;
            }
        } catch(\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir')."../web/install");
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

    protected function generateIndex($step, $page)
    {
        return $step*1000000 + $page;
    }

    protected function getStepAndPage($index) 
    {
        $step = intval($index/1000000);
        $page = $index%1000000;
        return array($step, $page);
    }

    protected function batchUpdate($index)
    {
        $batchUpdates = array(
            1 => 'changeQuestionTarget',
            2 => 'changeLessonMediaId',
            3 => 'initWapSetting'
        );
        if ($index == 0) {
            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '正在升级数据...',
                'progress' => 0
            );
        }

        list($step, $page) = $this->getStepAndPage($index);

        $method = $batchUpdates[$step];
        $page = $this->$method($page);

        if ($page == 1) {
            $step ++;
        }
        if ($step < 4) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '正在升级数据...',
                'progress' => 0
            );
        }
    }

//处理题目从属
    protected function changeQuestionTarget($page = 1)
    {
        $count = $this->searchSourceQuestionCount();
        $pageNum = self::$pageNum;
        $pages = intval(floor($count/$pageNum)) + ($count%$pageNum>0 ? 1 : 0);

        if ($page <= $pages) {
            $start = ($page-1) * $pageNum;

            $sourceQuestions = $this->getQuestionService()->searchQuestions(
                array('copyId' => 0), 
                array('createdTime', 'DESC'), 
                $start,
                $pageNum
            );

            foreach ($sourceQuestions as $sourceQuestion) {
                $questionTarget = explode('/', $sourceQuestion['target']);
                $num = count($questionTarget);
                //只有课时题目做处理
                if ($num > 1) {
                    $questionLessonTarget = explode('-', $questionTarget[1]);
                    $lessonId = $questionLessonTarget[1];
                    $lesson = $this->getLesson($lessonId);

                    if (empty($lesson)) {
                        $this->dealQuestionTarget($sourceQuestion);
                        $this->dealCopyQuestion($sourceQuestion);
                    }
                }
            }
            if ($page < $pages) {
                return ++$page;
            }
        }
        return 1;
    }

    private function searchSourceQuestionCount()
    {
        $sql = "select count(*) from question where copyId = 0";
        $count = $this->getConnection()->fetchAssoc($sql, array());
        return $count['count(*)'];
    }

    private function dealQuestionTarget($question)
    {
        $target = explode('/', $question['target']);
        return $this->getQuestionService()->updateQuestionTargetById($question['id'], array('target' => $target[0]));
    }

    private function dealCopyQuestion($sourceQuestion)
    {
        $copyQuestions = $this->findCopyQuestion($sourceQuestion);
        foreach ($copyQuestions as $copyQuestion) {
            $this->dealQuestionTarget($copyQuestion);
        }
    }

    private function getLesson($lessonId)
    {
        $sql = "select * from course_lesson where id = {$lessonId}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    protected function findCopyQuestion($sourceQuestion)
    {
        return $this->getQuestionService()->findQuestionsByCopyIds(array($sourceQuestion['id']));
    }

//处理课时试卷
    protected function changeLessonMediaId($page = 1)
    {
        $condition = array(
            'copyId' => 0,
            'type'   => 'testpaper',
            'startTime' => 1484064000
        );
        $connection = $this->getConnection();

        $count = $this->getCourseService()->searchLessonCount($condition);
        $pageNum = self::$pageNum;
        $pages = intval(floor($count/$pageNum)) + ($count%$pageNum>0 ? 1 : 0);

        if ($page <= $pages) {
            $start = ($page-1) * $pageNum;


            $sourceLessons = $this->findQuestionSourceLessons($condition, $start, $pageNum);

            foreach ($sourceLessons as $sourceLesson) {
                $copyLessons = $this->findCopyLessons($sourceLesson['id']);
                foreach ($copyLessons as $copyLesson) {
                    $target = "course-{$copyLesson['courseId']}";
                    $trueTestPaper = $this->getTestPaperByCopyIdAndTarget($sourceLesson['mediaId'], $target);
                    $this->updateCopyLessonMediaId($copyLesson['id'], $trueTestPaper['id']);
                }
            }
            if ($page < $pages) {
                return ++$page;
            }
        }
        return 1;
    }

    private function findQuestionSourceLessons($condition, $start, $pageNum)
    {
        $sourceLessons = $this->getCourseService()->searchLessons(
            $condition, 
            array('createdTime', 'DESC'), 
            $start, 
            $pageNum
        );

        return $sourceLessons;
    }
    private function updateCopyLessonMediaId($lessonId, $mediaId)
    {
        $sql = "update course_lesson set mediaId = {$mediaId} where id = {$lessonId}";
        return $this->getConnection()->executeQuery($sql, array());
    }

    private function getTestPaperByCopyIdAndTarget($copyId, $target)
    {
        $sql = "select * from testpaper where copyId = ? and target = ?";
        return $this->getConnection()->fetchAssoc($sql, array($copyId, $target));
    }

    private function findCopyLessons($lessonId)
    {
        $sql = "select * from course_lesson where copyId = {$lessonId}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    protected function initWapSetting()
    {
        $default = array(
            'enabled' => 1
        );

        $this->getSettingService()->set('wap', $default);

        return 1;
    }


    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getQuestionService()
    {
        return ServiceKernel::instance()->createService('Question.QuestionService');
    }
}

//抽象类
abstract class AbstractUpdater
{
    protected $kernel;
    public function __construct ($kernel)
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