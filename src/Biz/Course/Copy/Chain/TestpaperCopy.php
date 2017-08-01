<?php

namespace Biz\Course\Copy\Chain;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Copy\AbstractEntityCopy;
use Biz\Question\Service\QuestionService;
use Biz\Testpaper\Service\TestpaperService;

class TestpaperCopy extends AbstractEntityCopy
{
    /**
     * 复制链说明：
     * Testpaper 试卷/作业/练习
     * - TestpaperItem 题目列表
     *   - Question 题目内容.
     */

    /*
     * type='course-set'
     * - $source = originalCourse
     * - $config : newCourseSet
     *
     * type='course'
     * - $source = originalCourse
     * - $config : newCourse
     * */
    protected function copyEntity($source, $config = array())
    {
        return null;
    }

    protected function getFields()
    {
        return array(
            'name',
            'description',
            'limitedTime',
            'pattern',
            'status',
            'score',
            'passedCondition',
            'itemCount',
            'metas',
            'type',
        );
    }

    protected function baseCopyTestpaper($testpaper, $isCopy)
    {
        $newTestpaper = $this->filterFields($testpaper);

        $newTestpaper['lessonId'] = 0;
        $newTestpaper['createdUserId'] = $this->biz['user']['id'];
        $newTestpaper['copyId'] = $isCopy ? $testpaper['id'] : 0;

        return $newTestpaper;
    }

    protected function doCopyTestpaperItems($testpaperIds, $newTestpapers, $isCopy)
    {
        $items = $this->getTestpaperService()->findItemsByTestIds($testpaperIds);
        if (empty($items)) {
            return;
        }

        //$copyQuestions = $this->doCopyQuestions(ArrayToolkit::column($items, 'questionId'), $newTestpaper['courseSetId'], $isCopy);

        //班级复制把全部的题目都复制过去了
        $newTestpaper = current($newTestpapers);
        $copyQuestions = $this->getQuestionService()->findQuestionsByCourseSetId($newTestpaper['courseSetId']);
        $copyQuestions = ArrayToolkit::index($copyQuestions, 'copyId');

        $newItems = array();
        foreach ($items as $item) {
            $question = empty($copyQuestions[$item['questionId']]) ? array() : $copyQuestions[$item['questionId']];

            if (empty($question)) {
                continue;
            }

            $newTestpaper = $newTestpapers[$item['testId']];

            $newItem = array(
                'testId' => $newTestpaper['id'],
                'seq' => $item['seq'],
                'questionId' => $question['id'],
                'questionType' => $item['questionType'],
                'parentId' => $item['parentId'] > 0 ? $copyQuestions[$item['parentId']]['id'] : 0,
                'score' => $item['score'],
                'missScore' => $item['missScore'],
                'copyId' => $isCopy ? $item['id'] : 0,
                'type' => $item['type'],
            );

            $newItems[] = $newItem;
        }

        $this->getTestpaperService()->batchCreateItems($newItems);
    }

    /*
     * $ids = question ids
     * */
    protected function doCopyQuestions($ids, $newCourseSetId, $isCopy)
    {
        $copyQuestions = $this->getQuestionService()->findQuestionsByCourseSetId($newCourseSetId);

        $copyQuestions = ArrayToolkit::index($copyQuestions, 'copyId');
        $copyQuestionIds = ArrayToolkit::column($copyQuestions, 'copyId');

        $diff = array_values(array_diff($ids, $copyQuestionIds));
        if (empty($diff)) {
            return $copyQuestions;
        }

        $questions = $this->getQuestionService()->findQuestionsByIds($diff);
        $questions = $this->questionSort($questions);

        $questionMap = array();
        $newQuestions = array();
        foreach ($questions as $question) {
            $newQuestion = $this->filterQuestion($newCourseSetId, $question, $isCopy);

            $newQuestion['parentId'] = 0;
            if ($question['parentId'] > 0) {
                $newQuestion['parentId'] = isset($copyQuestions[$question['parentId']]) ? $copyQuestions[$question['parentId']]['id'] : $questionMap[$question['parentId']][0];
            }

            $newQuestions[] = $newQuestion;
        }

        $this->getQuestionService()->batchCreateQuestions($newQuestions);
        $questions = $this->getQuestionService()->findQuestionsByCourseSetId($newCourseSetId);
        $copyQuestions = ArrayToolkit::index($questions, 'copyId');

        return $copyQuestions;
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
            'difficulty',
        );

        $newQuestion = ArrayToolkit::parts($question, $fields);
        $newQuestion['courseSetId'] = $newCourseSetId;
        $newQuestion['courseId'] = 0;
        $newQuestion['lessonId'] = 0;
        $newQuestion['copyId'] = $isCopy ? $question['id'] : 0;
        $newQuestion['userId'] = $this->biz['user']['id'];
        $newQuestion['target'] = 'course-'.$newCourseSetId;

        return $newQuestion;
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->biz->service('Question:QuestionService');
    }
}
