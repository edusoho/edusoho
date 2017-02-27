<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Common\ArrayToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    private static $pageNum = 1500;

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
        return $step * 1000000 + $page;
    }

    protected function getStepAndPage($index)
    {
        $step = intval($index / 1000000);
        $page = $index % 1000000;
        return array($step, $page);
    }

    protected function batchUpdate($index)
    {
        $batchUpdates = array(
            1 => 'createTmpTable',
            2 => 'copyQuestionDataToTemplate',
            3 => 'dealQuestionTarget',
            4 => 'deleteTmpTable',
            5 => 'changeLessonMediaId',
            6 => 'initWapSetting'
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
        if ($step < 6) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '正在升级数据...',
                'progress' => 0
            );
        }
    }

    protected function createTmpTable()
    {
        $this->createTmpTableDao();
        return 1;
    }

    protected function copyQuestionDataToTemplate($page = 1)
    {
        $count = $this->searchSourceQuestionCount();
        $pageNum = self::$pageNum;
        $pages = intval(floor($count/$pageNum)) + ($count%$pageNum>0 ? 1 : 0);

        if ($page <= $pages) {
            $start = ($page - 1) * $pageNum;
            $questions = $this->searchSourceQuestion($start, $pageNum);
            foreach ($questions as $question) {
                $this->addTmpQuestion($question);
            }
            if ($page < $pages) {
                return ++$page;
            }
        }

        return 1;
    }

    protected function dealQuestionTarget($page = 1)
    {
        $count = $this->searchTmpQuestionCount();
        $pageNum = self::$pageNum;
        $pages = intval(floor($count/$pageNum)) + ($count%$pageNum>0 ? 1 : 0);

        if ($page <= $pages) {
            $start = ($page - 1) * $pageNum;
            $questions = $this->searchTmpQuestion($start, $pageNum);
            $lessonIds = ArrayToolkit::column($questions, 'lessonId');
            $lessons = $this->getCourseService()->findLessonsByIds($lessonIds);

            foreach ($questions as $question) {
                if (empty($lessons[$question['lessonId']])) {
                    $target = explode('/', $question['target']);
                    $fields = array('target' => $target[0]);
                    $this->getQuestionService()->updateQuestionTargetById($question['id'], $fields);
                }
            }

            if ($page < $pages) {
                return ++$page;
            }
        }

        return 1;
    }

    protected function deleteTmpTable()
    {
        $this->deleteTmpTableDao();
        return 1;
    }

    protected function changeLessonMediaId($page = 1)
    {
        $condition = array(
            'copyId' => 0,
            'type'   => 'testpaper',
            'startTime' => 1484064000
        );

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
                    if (!empty($trueTestPaper)) {
                        $this->updateCopyLessonMediaId($copyLesson['id'], $trueTestPaper['id']);
                    }
                }
            }
            if ($page < $pages) {
                return ++$page;
            }
        }
        return 1;
    }

    protected function initWapSetting()
    {
        $default = array(
            'enabled' => 1
        );
        $wap = $this->getSettingService()->get('wap', array());

        if (empty($wap)) {
            $this->getSettingService()->set('wap', $default);
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

    private function findCopyLessons($lessonId)
    {
        $sql = "select * from course_lesson where copyId = {$lessonId}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    private function getTestPaperByCopyIdAndTarget($copyId, $target)
    {
        $sql = "select * from testpaper where copyId = ? and target = ?";
        return $this->getConnection()->fetchAssoc($sql, array($copyId, $target));
    }

    private function updateCopyLessonMediaId($lessonId, $mediaId)
    {
        $sql = "update course_lesson set mediaId = {$mediaId} where id = {$lessonId}";
        return $this->getConnection()->executeQuery($sql, array());
    }

    private function searchSourceQuestionCount()
    {
        $sql = "select count(*) from question where target  REGEXP 'lesson-[0-9]+$'";
        return $this->getConnection()->fetchColumn($sql);
    }

    private function searchSourceQuestion($start, $limit)
    {
        $sql = "select id ,target, substring_index(substring_index(target,'/',-1),'-',-1) as lessonId FROM question WHERE target  REGEXP 'lesson-[0-9]+$' limit {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    private function searchTmpQuestionCount()
    {
        $sql = "SELECT COUNT(*) FROM `question_lesson_tmp`";
        return $this->getConnection()->fetchColumn($sql);
    }

    private function searchTmpQuestion($start, $limit)
    {
        $sql = "SELECT * FROM `question_lesson_tmp` limit {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    private function createTmpTableDao()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `question_lesson_tmp` ( 
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目ID',
                  `target` varchar(255) NOT NULL DEFAULT '' COMMENT '从属于',
                  `lessonId` int(10) unsigned NOT NULL COMMENT '课时ID',
                  PRIMARY KEY (`id`)
              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

        $this->getConnection()->exec($sql);
    }

    private function deleteTmpTableDao()
    {
        if ($this->isTableExist('question_lesson_tmp')) {
            $sql = "DROP TABLE `question_lesson_tmp`";
            $this->getConnection()->exec($sql);
        }
    }

    private function addTmpQuestion($tmp)
    {
        $this->getConnection()->insert('question_lesson_tmp', $tmp);
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    /**
     * @return \Topxia\Service\Question\Impl\QuestionServiceImpl
     */
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