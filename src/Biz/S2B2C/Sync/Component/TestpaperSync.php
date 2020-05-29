<?php

namespace Biz\S2B2C\Sync\Component;

use AppBundle\Common\ArrayToolkit;
use Biz\Question\Service\QuestionService;
use Biz\Testpaper\Dao\TestpaperDao;
use Biz\Testpaper\Dao\TestpaperItemDao;
use Biz\Testpaper\Service\TestpaperService;

class TestpaperSync extends AbstractEntitySync
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
    protected function syncEntity($source, $config = [])
    {
        return null;
    }

    protected function updateEntityToLastedVersion($source, $config = [])
    {
        return null;
    }

    protected function getFields()
    {
        return [
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
            'bankId',
        ];
    }

    protected function baseSyncTestpaper($testpaper)
    {
        $newTestpaper = $this->filterFields($testpaper);

        $newTestpaper['lessonId'] = 0;
        $newTestpaper['createdUserId'] = $this->biz['user']['id'];
        $newTestpaper['copyId'] = 0;
        $newTestpaper['syncId'] = $testpaper['id'];

        return $newTestpaper;
    }

    protected function doSyncTestpaperItems($newTestpapers, $isCopy, $questionSyncIds)
    {
        $newTestpaper = current($newTestpapers);
        $items = $newTestpaper['items'];
        if (empty($items)) {
            return;
        }

        $copyQuestions = $this->getQuestionService()->findQuestionsBySyncIds($questionSyncIds);

        $newItems = [];
        foreach ($items as $item) {
            $question = empty($copyQuestions[$item['questionId']]) ? [] : $copyQuestions[$item['questionId']];

            if (empty($question)) {
                continue;
            }

            $newTestpaper = $newTestpapers[$item['testId']];
            $parentId = empty($item['parentId']) ? 0 : $copyQuestions[$item['parentId']]['id'];

            $newItem = [
                'testId' => $newTestpaper['id'],
                'seq' => $item['seq'],
                'questionId' => $question['id'],
                'questionType' => $item['questionType'],
                'parentId' => $parentId,
                'score' => $item['score'],
                'missScore' => $item['missScore'],
                'copyId' => 0,
                'type' => $item['type'],
                'syncId' => $item['id'],
            ];

            $newItems[] = $newItem;
        }

        $this->getTestpaperService()->batchCreateItems($newItems);
    }

    protected function doUpdateTestpaperItems($newTestpapers, $isCopy, $questionSyncIds)
    {
        $newTestpaper = current($newTestpapers);
        $items = $newTestpaper['items'];
        $existItems = $this->getItemDao()->search(['testId' => $newTestpaper['id']], [], 0, PHP_INT_MAX);
        if (empty($items)) {
            foreach ($existItems as $existItem) {
                $this->getItemDao()->delete($existItem['id']);
            }

            return;
        }

        $copyQuestions = $this->getQuestionService()->findQuestionsBySyncIds($questionSyncIds);

        $existItems = ArrayToolkit::index($existItems, 'syncId');

        $newItems = [];
        foreach ($items as $item) {
            $question = empty($copyQuestions[$item['questionId']]) ? [] : $copyQuestions[$item['questionId']];

            if (empty($question)) {
                continue;
            }

            $newTestpaper = $newTestpapers[$item['testId']];
            $parentId = empty($item['parentId']) ? 0 : $copyQuestions[$item['parentId']]['id'];

            $newItem = [
                'testId' => $newTestpaper['id'],
                'seq' => $item['seq'],
                'questionId' => $question['id'],
                'questionType' => $item['questionType'],
                'parentId' => $parentId,
                'score' => $item['score'],
                'missScore' => $item['missScore'],
                'copyId' => 0,
                'type' => $item['type'],
                'syncId' => $item['id'],
            ];
            if (!empty($existItems[$newItem['syncId']])) {
                $this->getItemDao()->update($existItems[$newItem['syncId']]['id'], $newItem);
                continue;
            }

            $newItems[] = $newItem;
        }

        $this->getTestpaperService()->batchCreateItems($newItems);

        $needDeleteItemSyncIds = array_values(array_diff(array_keys($existItems), ArrayToolkit::column($items, 'id')));
        if (!empty($existItems) && !empty($needDeleteItemSyncIds)) {
            $needDeleteItems = $this->getItemDao()->search(['testId' => $newTestpaper['id'], 'syncIds' => $needDeleteItemSyncIds], [], 0, PHP_INT_MAX);
            foreach ($needDeleteItems as $needDeleteItem) {
                $this->getItemDao()->delete($needDeleteItem['id']);
            }
        }
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
    protected function getItemDao()
    {
        return $this->biz->dao('Testpaper:TestpaperItemDao');
    }
}
