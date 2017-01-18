<?php

namespace Biz\Course\Copy\Impl;

use Topxia\Common\ArrayToolkit;
use Biz\Question\Dao\QuestionDao;
use Biz\Testpaper\Dao\TestpaperDao;
use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Testpaper\Dao\TestpaperItemDao;

class TestpaperCopy extends AbstractEntityCopy
{
    private $type;

    /**
     * 复制链说明：
     * Testpaper 试卷/作业/练习
     * - TestpaperItem 题目列表
     *   - Question 题目内容
     * @param $biz
     * @param $type
     */
    public function __construct($biz, $type)
    {
        $this->biz  = $biz;
        $this->type = $type;
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

    private function baseCopyTestpaper($testpaper)
    {
        return array(
            'name'            => $testpaper['name'],
            'description'     => $testpaper['description'],
            'lessonId'        => 0,
            'limitedTime'     => $testpaper['limitedTime'],
            'pattern'         => $testpaper['pattern'],
            'target'          => $testpaper['target'],
            'status'          => $testpaper['status'],
            'score'           => $testpaper['score'],
            'passedCondition' => $testpaper['passedCondition'],
            'itemCount'       => $testpaper['itemCount'],
            'createdUserId'   => $this->biz['user']['id'],
            'metas'           => $testpaper['metas'],
            'copyId'          => $testpaper['id'],
            'type'            => $testpaper['type']
        );
    }

    private function doCopyTestpaperItems($testpaper, $newTestpaper)
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
                'missScore'    => $item['missScore'],
                'copyId'       => $item['id']
            );

            $this->getTestpaperItemDao()->create($newItem);
        }
    }

    /*
     * $ids = question ids
     * */
    private function doCopyQuestions($ids, $newCourseId)
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
        foreach ($questions as $question) {
            $newQuestion = array(
                'type'       => $question['type'],
                'stem'       => $question['stem'],
                'score'      => $question['score'],
                'answer'     => $question['answer'],
                'analysis'   => $question['analysis'],
                'metas'      => $question['metas'],
                'categoryId' => $question['categoryId'],
                'difficulty' => $question['difficulty'],
                'target'     => $question['target'],
                'courseId'   => $newCourseId,
                'lessonId'   => 0,
                'copyId'     => $question['id'],
                'userId'     => $this->biz['user']['id']
            );

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
