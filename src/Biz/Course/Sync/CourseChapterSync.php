<?php

namespace Biz\Course\Sync;

use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Dao\CourseDao;
use Biz\Synchronization\Service\AbstractSychronizer;
use Codeages\Biz\Framework\Context\BizAware;

class CourseChapterSync extends AbstractSychronizer
{
    public function syncWhenCreated($sourceId)
    {
        $sourceChapter = $this->getCourseChapterDao();
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($sourceChapter['courseId'], 1);

        foreach ($copiedCourses as $copyCourse) {
            $copyChapter = $sourceChapter;
            $copyChapter['courseId'] = $copyCourse['id'];
            $copyChapter['copyId'] = $sourceChapter['id'];
            unset($copyChapter['id']);
            $this->getBatchHelper(self::BATCH_CREATE_HELPER, $this->getCourseChapterDao())->;
        }

    }

    public function syncWhenUpdated($sourceId)
    {
        // TODO: Implement syncWhenUpdated() method.
    }

    public function syncWhenDeleted($sourceId)
    {
        // TODO: Implement syncWhenDeleted() method.
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