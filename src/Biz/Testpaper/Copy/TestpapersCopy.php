<?php

namespace Biz\Testpaper\Copy;

use Biz\AbstractCopy;
use AppBundle\Common\ArrayToolkit;

class TestpapersCopy extends AbstractCopy
{
    public function doCopy($source, $options)
    {
        return $this->cloneCourseSetTestpapers($source, $options);
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
            'courseId', //先复制courseId、LessonId，最后更新成新的
            'lessonId',
            'metas',
            'type',
        );
    }

    public function preCopy($source, $options)
    {
    }

    private function cloneCourseSetTestpapers($source, $options)
    {
        $newCourseSet = $options['newCourseSet'];
        $conditions = array(
            'courseSetId' => $source['id'],
            'type' => 'testpaper',
        );
        $testpapers = $this->getTestpaperDao()->search($conditions, array(), 0, PHP_INT_MAX);

        if (empty($testpapers)) {
            return;
        }

        $user = $this->biz['user'];
        $newTestpapers = array();
        $testpaperIds = array();
        foreach ($testpapers as $testpaper) {
            $newTestpaper = $this->partsFields($testpaper);
            $newTestpaper['courseSetId'] = $newCourseSet['id'];
            $newTestpaper['target'] = 'course-'.$newCourseSet['id'];
            $newTestpaper['createdUserId'] = $user['id'];
            $newTestpaper['updatedUserId'] = $user['id'];
            $newTestpaper['copyId'] = $testpaper['id'];

            $testpaperIds[] = $testpaper['id'];
            $newTestpapers[] = $newTestpaper;
        }
        if (!empty($newTestpapers)) {
            $this->getTestpaperDao()->batchCreate($newTestpapers);
            $newTestpapers = $this->getTestpaperDao()->search(array('courseSetId' => $newCourseSet['id']), array(), 0, PHP_INT_MAX);
            $this->cloneTestpaperItems($testpapers, $newTestpapers, $newCourseSet);
        }
    }

    protected function cloneTestpaperItems($testpapers, $newTestpapers, $newCourseSet)
    {
        $testpaperIds = ArrayToolkit::column($testpapers, 'id');
        $newTestpapers = ArrayToolkit::index($newTestpapers, 'copyId');
        $items = $this->getTestpaperItemDao()->findItemsByTestIds($testpaperIds);
        if (empty($items)) {
            return;
        }

        $copyQuestions = $this->getQuestionDao()->findQuestionsByCourseSetId($newCourseSet['id']);
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
                'type' => $item['type'],
            );

            $newItems[] = $newItem;
        }

        $this->getTestpaperItemDao()->batchCreate($newItems);
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
