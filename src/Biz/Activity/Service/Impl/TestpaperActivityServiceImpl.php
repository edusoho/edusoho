<?php

namespace Biz\Activity\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\TestpaperActivityDao;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\BaseService;

class TestpaperActivityServiceImpl extends BaseService implements TestpaperActivityService
{
    public function getActivity($id)
    {
        return $this->getTestpaperActivityDao()->get($id);
    }

    public function findActivitiesByIds($ids)
    {
        return $this->getTestpaperActivityDao()->findByIds($ids);
    }

    public function findActivitiesByMediaIds($mediaIds)
    {
        $activities = $this->getTestpaperActivityDao()->findByMediaIds($mediaIds);

        return ArrayToolkit::index($activities, 'mediaId');
    }

    public function createActivity($fields)
    {
        return $this->getTestpaperActivityDao()->create($fields);
    }

    public function updateActivity($id, $fields)
    {
        return $this->getTestpaperActivityDao()->update($id, $fields);
    }

    public function deleteActivity($id)
    {
        return $this->getTestpaperActivityDao()->delete($id);
    }

    public function getActivityByAnswerSceneId($answerSceneId)
    {
        return $this->getTestpaperActivityDao()->getActivityByAnswerSceneId($answerSceneId);
    }

    public function findByAnswerSceneIds($answerSceneIds)
    {
        return $this->getTestpaperActivityDao()->findByAnswerSceneIds($answerSceneIds);
    }

    /**
     * @return TestpaperActivityDao
     */
    protected function getTestpaperActivityDao()
    {
        return $this->createDao('Activity:TestpaperActivityDao');
    }
}
