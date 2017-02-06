<?php

namespace Biz\Course\Copy\Impl;

use Topxia\Common\ArrayToolkit;
use Biz\Course\Copy\AbstractEntityCopy;

class QuestionCopy extends AbstractEntityCopy
{
    /**
     * 复制链说明：
     * Question
     * - Question attachment 问题附件
     * @param $biz
     */
    public function __construct($biz, $node)
    {
        parent::__construct($biz, $node);
    }

    protected function _copy($source, $config = array())
    {
        $newCourse = $config['newCourse'];
        return $this->doCopyQuestions($newCourse['courseSetId'], $source['id'], $config['isCopy']);
    }

    /*
     * $ids = question ids
     * */
    protected function doCopyQuestions($newCourseSetId, $courseId, $isCopy)
    {
        $testpapers = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'testpaper');
        $others     = $this->getActivityService()->search(array('fromCourseId' => $courseId, 'mediaTypes' => array('homework', 'exercise')), array(), 0, PHP_INT_MAX);

        $testpaperExt = $this->getTestpaperActivityService()->findActivitiesByIds(ArrayToolkit::column($testpapers, 'mediaId'));

        $testpaperIds = array_merge(ArrayToolkit::column($testpaperExt, 'mediaId'), ArrayToolkit::column($others, 'mediaId'));
        if (empty($testpaperIds)) {
            return array();
        }

        $testpaperItems = $this->getTestpaperService()->findItemsByTestIds($testpaperIds);
        $questionIds    = ArrayToolkit::column($testpaperItems, 'questionId');
        if (empty($questionIds)) {
            return array();
        }

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);
        $questions = $this->questionSort($questions);

        $questionMap = array();
        foreach ($questions as $question) {
            $newQuestion = $this->filterFields($newCourseSetId, $question, $isCopy);

            $newQuestion['parentId'] = $question['parentId'] > 0 ? $questionMap[$question['parentId']][0] : 0;

            $newQuestion = $this->getQuestionService()->create($newQuestion);

            $questionMap[$question['id']] = array($newQuestion['id'], $newQuestion['parentId']);
        }

        return $questionMap;
    }

    private function questionSort($questions)
    {
        usort($questions, function ($a, $b) {
            if ($a['parentId'] == $b['parentId']) {
                return 0;
            }
            return $a['parentId'] < $b['parentId'] ? -1 : 1;
        });

        return $questions;
    }

    private function filterFields($newCourseSetId, $question, $isCopy)
    {
        $fields = array(
            'type',
            'stem',
            'score',
            'answer',
            'analysis',
            'metas',
            'categoryId',
            'difficulty'
        );

        $newQuestion             = ArrayToolkit::parts($question, $fields);
        $newQuestion['courseId'] = $newCourseSetId;
        $newQuestion['lessonId'] = 0;
        $newQuestion['copyId']   = $isCopy ? $question['id'] : 0;
        $newQuestion['userId']   = $this->biz['user']['id'];
        $newQuestion['target']   = 'course-'.$newCourseSetId;

        return $newQuestion;
    }

    protected function getTestpaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }

    protected function getTestpaperActivityService()
    {
        return $this->biz->service('Activity:TestpaperActivityService');
    }

    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    protected function getQuestionService()
    {
        return $this->biz->service('Question:QuestionService');
    }
}
