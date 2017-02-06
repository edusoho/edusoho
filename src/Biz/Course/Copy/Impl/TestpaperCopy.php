<?php

namespace Biz\Course\Copy\Impl;

use Topxia\Common\ArrayToolkit;
use Biz\Course\Copy\AbstractEntityCopy;

class TestpaperCopy extends AbstractEntityCopy
{
    /**
     * 复制链说明：
     * Testpaper 试卷/作业/练习
     * - TestpaperItem 题目列表
     *   - Question 题目内容
     * @param $biz
     */
    public function __construct($biz, $node)
    {
        parent::__construct($biz, $node);
    }

    /*
     * type='course-set'
     * - $source = originalCourse
     * - $config : newCourseSet
     *
     * type='course'
     * - $source = originalCourse
     * - $config : newCourse
     * */
    protected function _copy($source, $config = array())
    {
        return null;
    }

    protected function baseCopyTestpaper($testpaper, $isCopy)
    {
        $fields = array(
            'name',
            'description',
            'limitedTime',
            'pattern',
            'status',
            'score',
            'passedCondition',
            'itemCount',
            'metas',
            'type'
        );
        $newTestpaper = array(
            'lessonId'      => 0,
            'createdUserId' => $this->biz['user']['id'],
            'copyId'        => $isCopy ? $testpaper['id'] : 0
        );
        foreach ($fields as $field) {
            if (!empty($testpaper[$field]) || $testpaper[$field] == 0) {
                $newTestpaper[$field] = $testpaper[$field];
            }
        }
        return $newTestpaper;
    }

    protected function doCopyTestpaperItems($testpaper, $newTestpaper, $isCopy)
    {
        $items = $this->getTestpaperService()->findItemsByTestId($testpaper['id']);
        if (empty($items)) {
            return;
        }

        //$this->doCopyQuestions(ArrayToolkit::column($items, 'questionId'), $newTestpaper['courseSetId'], $isCopy);

        $copyQuestions = $this->getQuestionService()->findQuestionsByCourseSetId($newTestpaper['courseSetId']);
        $copyQuestions = ArrayToolkit::index($copyQuestions, 'copyId');

        foreach ($items as $item) {
            $question = empty($copyQuestions[$item['questionId']]) ? array() : $copyQuestions[$item['questionId']];

            if (empty($question)) {
                continue;
            }
            $newItem = array(
                'testId'       => $newTestpaper['id'],
                'seq'          => $item['seq'],
                'questionId'   => $question['id'],
                'questionType' => $item['questionType'],
                'parentId'     => $item['parentId'] > 0 ? $copyQuestions[$item['parentId']]['id'] : 0,
                'score'        => $item['score'],
                'missScore'    => $item['missScore'],
                'copyId'       => $isCopy ? $item['id'] : 0
            );

            $this->getTestpaperService()->createItem($newItem);
        }
    }

    /*
     * $ids = question ids
     * */
    protected function doCopyQuestions($ids, $newCourseSetId, $isCopy)
    {
        $copyQuestions = $this->getQuestionService()->findQuestionsByCourseSetId($newCourseSetId);

        $copyQuestionIds = ArrayToolkit::column($copyQuestions, 'copyId');

        $diff = array_values(array_diff($ids, $copyQuestionIds));
        if (empty($diff)) {
            return array();
        }

        $copyQuestions = ArrayToolkit::index($copyQuestions, 'copyId');
        $questions     = $this->getQuestionService()->findQuestionsByIds($diff);
        $questions     = $this->questionSort($questions);

        $questionMap = array();
        foreach ($questions as $question) {
            $newQuestion = $this->filterQuestion($newCourseSetId, $question, $isCopy);

            $newQuestion['parentId'] = 0;
            if ($question['parentId'] > 0) {
                $newQuestion['parentId'] = isset($copyQuestions[$question['parentId']]) ? $copyQuestions[$question['parentId']]['id'] : $questionMap[$question['parentId']][0];
            }

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

    private function filterQuestion($newCourseSetId, $question, $isCopy)
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

    protected function getQuestionService()
    {
        return $this->biz->service('Question:QuestionService');
    }
}
