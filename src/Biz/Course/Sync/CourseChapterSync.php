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

        $helper = $this->getBatchHelper(self::BATCH_CREATE_HELPER, $this->getCourseChapterDao());
        foreach ($copiedCourses as $copyCourse) {
            $copyChapter = $sourceChapter;
            $copyChapter['courseId'] = $copyCourse['id'];
            $copyChapter['copyId'] = $sourceChapter['id'];
            unset($copyChapter['id']);
            $helper->add($copyChapter);
            unset($copyChapter);
        }

        unset($copiedCourses);
    }

    public function syncWhenUpdated($sourceId)
    {
        $sourceChapter = $this->getCourseChapterDao();
        $copiedChapters = $this->getCourseChapterDao()->findByCopyId($sourceId);

        $helper = $this->getBatchHelper(self::BATCH_UPDATE_HELPER, $this->getCourseChapterDao());
        foreach ($copiedChapters as $copiedChapter) {
            $newFields = $sourceChapter;
            unset($newFields['id']);
            unset($newFields['courseId']);
            unset($newFields['copyId']);
            $helper->add('id', $copiedChapter['id'], $newFields);

            unset($newFields);
        }

        unset($copiedChapters);
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