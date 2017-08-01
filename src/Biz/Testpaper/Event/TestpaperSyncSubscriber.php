<?php

namespace Biz\Testpaper\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Testpaper\Dao\TestpaperDao;
use Biz\Testpaper\Dao\TestpaperItemDao;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Event\CourseSyncSubscriber;

class TestpaperSyncSubscriber extends CourseSyncSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            // 'testpaper.create'      => 'onTestpaperCreate',
            'exam.update' => 'onTestpaperUpdate',
            'exam.delete' => 'onTestpaperDelete',
            'exam.publish' => 'onTestpaperUpdate',
            'exam.close' => 'onTestpaperUpdate',

            'testpaper.item.create' => 'onTestpaperItemCreate',
            'testpaper.item.update' => 'onTestpaperItemUpdate',
            'testpaper.item.delete' => 'onTestpaperItemDelete',
        );
    }

    public function onTestpaperUpdate(Event $event)
    {
        $testpaper = $event->getSubject();
        if ($testpaper['copyId'] > 0 || in_array($testpaper['type'], array('homework', 'exercise'))) {
            return;
        }

        $copiedCourseSets = $this->getCourseSetDao()->findCourseSetsByParentIdAndLocked($testpaper['courseSetId'], 1);
        if (empty($copiedCourseSets)) {
            return;
        }

        $copiedCourseSetIds = ArrayToolkit::column($copiedCourseSets, 'id');

        $copiedTestpapers = $this->getTestpaperDao()->findTestpapersByCopyIdAndCourseSetIds($testpaper['id'], $copiedCourseSetIds);

        if (empty($copiedTestpapers)) {
            return;
        }

        foreach ($copiedTestpapers as $ct) {
            $copyTestpaper = $this->copyFields($testpaper, $ct, array(
                'name',
                'description',
                'courseId',
                'lessonId',
                'limitedTime',
                'pattern',
                'target',
                'status',
                'score',
                'passedCondition',
                'itemCount',
                'updatedUserId',
                'metas',
            ));

            $this->getTestpaperDao()->update($ct['id'], $copyTestpaper);
        }
    }

    public function onTestpaperDelete(Event $event)
    {
    }

    public function onTestpaperItemCreate(Event $event)
    {
        $item = $event->getSubject();
        if ($item['copyId'] > 0) {
            return;
        }
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($item['courseId'], 1);
        if (empty($copiedCourses)) {
            return;
        }
        $copiedCourseSetIds = ArrayToolkit::column($copiedCourses, 'courseSetId');
        $copiedTestpapers = $this->getTestpaperDao()->findTestpapersByCopyIdAndCourseSetIds($item['copyId'], $copiedCourseSetIds);

        foreach ($copiedTestpapers as $ct) {
            $newItem = array(
                'testId' => $ct['id'],
                'seq' => $item['seq'],
                'questionId' => $item['questionId'], //fixme get question.id by copyId and courseId
                'questionType' => $item['questionType'],
                'parentId' => $item['parentId'], //fixme
                'score' => $item['score'],
                'missScore' => $item['missScore'],
                'copyId' => $item['id'],
            );
            $this->getTestpaperItemDao()->create($newItem);
        }
    }

    public function onTestpaperItemUpdate(Event $event)
    {
        $item = $event->getSubject();
        if ($item['copyId'] > 0) {
            return;
        }
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($item['courseId'], 1);
        if (empty($copiedCourses)) {
            return;
        }
        $copiedCourseSetIds = ArrayToolkit::column($copiedCourses, 'courseSetId');
        $copiedTestpapers = $this->getTestpaperDao()->findTestpapersByCopyIdAndCourseSetIds($item['copyId'], $copiedCourseSetIds);
        $copiedItems = $this->getTestpaperItemDao()->findTestpaperItemsByCopyIdAndLockedTestIds($item['id'], ArrayToolkit::column($copiedTestpapers, 'id'));
        if (empty($copiedItems)) {
            return;
        }
        foreach ($copiedItems as $ci) {
            $ci = $this->copyFields($item, $ci, array(
                'seq',
                'score',
                'missScore',
            ));
            $this->getTestpaperItemDao()->update($ci['id'], $ci);
        }
    }

    public function onTestpaperItemDelete(Event $event)
    {
        $item = $event->getSubject();
        if ($item['copyId'] > 0) {
            return;
        }
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($item['courseId'], 1);
        if (empty($copiedCourses)) {
            return;
        }
        $copiedCourseSetIds = ArrayToolkit::column($copiedCourses, 'courseSetId');
        $copiedTestpapers = $this->getTestpaperDao()->findTestpapersByCopyIdAndCourseSetIds($item['copyId'], $copiedCourseSetIds);
        $copiedItems = $this->getTestpaperItemDao()->findTestpaperItemsByCopyIdAndLockedTestIds($item['id'], ArrayToolkit::column($copiedTestpapers, 'id'));
        if (empty($copiedItems)) {
            return;
        }
        foreach ($copiedItems as $ci) {
            $this->getTestpaperItemDao()->delete($ci['id']);
        }
    }

    /**
     * @return TestpaperDao
     */
    protected function getTestpaperDao()
    {
        return $this->getBiz()->dao('Testpaper:TestpaperDao');
    }

    /**
     * @return TestpaperItemDao
     */
    protected function getTestpaperItemDao()
    {
        return $this->getBiz()->dao('Testpaper:TestpaperItemDao');
    }
}
