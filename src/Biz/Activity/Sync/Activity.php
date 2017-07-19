<?php

namespace Biz\Course\Sync;

use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Dao\CourseDao;
use Biz\Sync\Service\AbstractSychronizer;

class Activity extends AbstractSychronizer
{
    public function syncWhenCreate($sourceId)
    {
        $sourceAct = $this->getActivityService()->getActivity($sourceId, true);
        $copiedCourses = $this->getCourseDao()->findCoursesByParentIdAndLocked($sourceAct['fromCourseId'], 1);

        $activityHelper = $this->getBatchHelper(self::BATCH_CREATE_HELPER, $this->getActivityDao());
        foreach ($copiedCourses as $copyCourse) {
            $copyExt = $sourceAct;
            $copyExt['fromCourseId'] = $copyCourse['id'];
            $copyExt['fromCourseSetId'] = $copyCourse['courseSetId'];
            $copyExt['copyId'] = $sourceAct['id'];
            $ext = $this->copyExt($sourceAct);
            $copyExt['mediaId'] = $ext['id'];
            unset($copyExt['id']);

            $activityHelper->add($copyExt);

            unset($copyExt);
            unset($ext);
        }

        unset($copiedCourses);
    }

    public function syncWhenUpdate($sourceId)
    {

    }

    private function copyExt($sourceAct)
    {
        $ext = $sourceAct['ext'];
        return $this->getActivityExtDao($sourceAct['mediaType'])->create($ext);
    }

    public function syncWhenDelete($sourceId)
    {
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }

    /**
     * @return CourseDao
     */
    private function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }

    private function getActivityExtDao($type)
    {
        return $this->biz->dao("Activity:{$type}ActivityDao");
    }
}