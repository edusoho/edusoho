<?php

namespace Biz\Course\Sync;

use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Dao\CourseDao;
use Biz\Sync\Service\AbstractSychronizer;

class CourseChapter extends AbstractSychronizer
{
    public function syncWhenCreate($sourceId)
    {
        $sourceChapter = $this->getCourseChapterDao()->get($sourceId);
        if (empty($sourceChapter)) {
            return;
        }

        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($sourceChapter['courseId'], 1);
        if (empty($copiedCourses)) {
            return;
        }
        $helper = $this->getBatchHelper(self::BATCH_CREATE_HELPER, $this->getCourseChapterDao());

        $syncChapter = $this->filterSyncChapter($sourceChapter);
        $syncChapter['copyId'] = $sourceChapter['id'];
        foreach ($copiedCourses as $copyCourse) {
            $syncChapter['courseId'] = $copyCourse['id'];
            $helper->add($syncChapter);
        }
    }

    public function syncWhenUpdate($sourceId)
    {
        $sourceChapter = $this->getCourseChapterDao()->get($sourceId);
        if (empty($sourceChapter)) {
            return;
        }
        $syncChapters = $this->getCourseChapterDao()->findByCopyId($sourceId);
        if (empty($syncChapters)) {
            return;
        }
        $this->getCourseChapterDao()->update(['copyId' => $sourceId], $this->filterSyncChapter($sourceChapter));
    }

    public function syncWhenDelete($sourceId)
    {
        $this->getCourseChapterDao()->batchDelete(['copyId' => $sourceId]);
    }

    private function filterSyncChapter($sourceChapter)
    {
        unset($sourceChapter['id']);
        unset($sourceChapter['courseId']);
        unset($sourceChapter['copyId']);
        unset($sourceChapter['createdTime']);
        unset($sourceChapter['updatedTime']);

        return $sourceChapter;
    }

    /**
     * @return CourseChapterDao
     */
    private function getCourseChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }

    /**
     * @return CourseDao
     */
    private function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }
}
