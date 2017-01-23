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
            'target',
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

        $questionMap = $this->doCopyQuestions(ArrayToolkit::column($items, 'questionId'), $newTestpaper['courseId'], $isCopy);
        foreach ($items as $item) {
            $question = empty($questionMap[$item['questionId']]) ? array() : $questionMap[$item['questionId']];

            if (empty($question)) {
                continue;
            }
            $newItem = array(
                'testId'       => $newTestpaper['id'],
                'seq'          => $item['seq'],
                'questionId'   => $question[0],
                'questionType' => $item['questionType'],
                'parentId'     => $question[1],
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
    protected function doCopyQuestions($ids, $newCourseId, $isCopy)
    {
        $questions   = $this->getQuestionService()->findQuestionsByIds($ids);
        $questionMap = array();
        if (empty($questions)) {
            return $questionMap;
        }

        usort($questions, function ($a, $b) {
            if ($a['parentId'] == $b['parentId']) {
                return 0;
            }
            return $a['parentId'] < $b['parentId'] ? -1 : 1;
        });

        $fields = array(
            'type',
            'stem',
            'score',
            'answer',
            'analysis',
            'metas',
            'categoryId',
            'difficulty',
            'target'
        );
        foreach ($questions as $question) {
            $newQuestion = array(
                'courseId' => $newCourseId,
                'lessonId' => 0,
                'copyId'   => $isCopy ? $question['id'] : 0,
                'userId'   => $this->biz['user']['id']
            );
            foreach ($fields as $field) {
                if (!empty($question[$field]) || $question[$field] == 0) {
                    $newQuestion[$field] = $question[$field];
                }
            }

            $newQuestion['parentId'] = $question['parentId'] > 0 ? $questionMap[$question['parentId']][0] : 0;

            //$newQuestion = $this->getQuestionDao()->create($newQuestion);
            $newQuestion = $this->getQuestionService()->create($newQuestion);

            $questionMap[$question['id']] = array($newQuestion['id'], $newQuestion['parentId']);
        }

        return $questionMap;
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
