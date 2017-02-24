<?php

use Phpmig\Migration\Migration;
use Topxia\Common\Paginator;
use Topxia\Service\Common\ServiceKernel;

class ChangeQuestionTarget extends Migration
{
    protected $num = 1000;

    /**
     * Do the migration
     */
    public function up()
    {
        $sourceQuestionCount = $this->searchSourceQuestionCount();
        for ($i=0; $i < $sourceQuestionCount/$this->num; $i++) {
            $sourceQuestions = $this->getQuestionService()->searchQuestions(
                array('copyId' => 0),
                array('createdTime', 'DESC'),
                0,
                $this->num*($i + 1)
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
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
    
    private function searchSourceQuestionCount()
    {
        $sql = "select count(*) from question where copyId = 0";
        $count = $this->db()->fetchAssoc($sql, array());
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
        return $this->db()->fetchAll($sql, array());
    }

    protected function findCopyQuestion($sourceQuestion)
    {
        return $this->getQuestionService()->findQuestionsByCopyIds(array($sourceQuestion['id']));
    }

    protected function getBiz()
    {
        return $this->getContainer();
    }

    protected function db()
    {
        $biz = $this->getBiz();
        return $biz['db'];
    }

    protected function getQuestionService()
    {
        return ServiceKernel::instance()->createService('Question.QuestionService');
    }
}
