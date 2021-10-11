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

        try {
            $this->getLock()->get("sync_course_{$sourceChapter['courseId']}", 10);

            $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($sourceChapter['courseId'], 1);
            if (empty($copiedCourses)) {
                $this->getLock()->release("sync_course_{$sourceChapter['courseId']}");

                return;
            }

            $this->beginTransaction();

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

            $this->commit();
            $this->getLock()->release("sync_course_{$sourceChapter['courseId']}");
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLock()->release("sync_course_{$sourceChapter['courseId']}");
        }
    }

    public function syncWhenUpdate($sourceId)
    {
        $sourceChapter = $this->getCourseChapterDao()->get($sourceId);

        try {
            $this->getLock()->get("sync_course_{$sourceChapter['courseId']}", 10);

            $copiedChapters = $this->getCourseChapterDao()->findByCopyId($sourceId);

            if (empty($copiedChapters)) {
                $this->getLock()->release("sync_course_{$sourceChapter['courseId']}");

                return;
            }
            $this->beginTransaction();

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

            $this->commit();
            $this->getLock()->release("sync_course_{$sourceChapter['courseId']}");
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLock()->release("sync_course_{$sourceChapter['courseId']}");
            throw $e;
        }
    }

    public function syncWhenDelete($sourceId)
    {
        $this->getCourseChapterDao()->batchDelete(['copyId' => $sourceId]);
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
