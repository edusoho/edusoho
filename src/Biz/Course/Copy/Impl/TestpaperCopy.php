<?php

namespace Biz\Course\Copy\Impl;

use Topxia\Common\ArrayToolkit;
use Biz\Question\Dao\QuestionDao;
use Biz\Testpaper\Dao\TestpaperDao;
use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Testpaper\Dao\TestpaperItemDao;

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
        $this->biz = $biz;
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

    protected function baseCopyTestpaper($testpaper)
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
            'copyId'        => $testpaper['id']
        );
        foreach ($fields as $field) {
            if (!empty($testpaper[$field]) || $testpaper[$field] == 0) {
                $newTestpaper[$field] = $testpaper[$field];
            }
        }
        return $newTestpaper;
    }

    protected function doCopyTestpaperItems($testpaper, $newTestpaper)
    {
        $items = $this->getTestpaperItemDao()->findItemsByTestId($testpaper['id']);
        if (empty($items)) {
            return;
        }

        $questionMap = $this->doCopyQuestions(ArrayToolkit::column($items, 'questionId'), $newTestpaper['courseId']);
        foreach ($items as $item) {
            $newItem = array(
                'testId'       => $newTestpaper['id'],
                'seq'          => $item['seq'],
                'questionId'   => $questionMap[$item['questionId']][0],
                'questionType' => $item['questionType'],
                'parentId'     => $questionMap[$item['questionId']][1],
                'score'        => $item['score'],
                'missScore'    => $item['missScore']
                // 'copyId'       => $item['id']
            );

            $this->getTestpaperItemDao()->create($newItem);
        }
    }

    /*
     * $ids = question ids
     * */
    protected function doCopyQuestions($ids, $newCourseId)
    {
        $questions   = $this->getQuestionDao()->findQuestionsByIds($ids);
        $questionMap = array();
        if (empty($questions)) {
            return $questionMap;
        }

        usort($questions, function ($a, $b) {
            //@todo 这个逻辑待测试
            return $a['parentId'] < $b['parentId'];
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
                'copyId'   => $question['id'],
                'userId'   => $this->biz['user']['id']
            );
            foreach ($fields as $field) {
                if (!empty($question[$field]) || $question[$field] == 0) {
                    $newQuestion[$field] = $question[$field];
                }
            }

            $newQuestion['parentId'] = $question['parentId'] > 0 ? $questionMap[$question['parentId']] : 0;

            $newQuestion = $this->getQuestionDao()->create($newQuestion);

            $questionMap[$question['id']] = array($newQuestion['id'], $newQuestion['parentId']);
        }

        return $questionMap;
    }

    /**
     * @return TestpaperDao
     */
    protected function getTestpaperDao()
    {
        return $this->biz->dao('Testpaper:TestpaperDao');
    }

    /**
     * @return TestpaperItemDao
     */
    protected function getTestpaperItemDao()
    {
        return $this->biz->dao('Testpaper:TestpaperItemDao');
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->biz->dao('Question:QuestionDao');
    }
}
