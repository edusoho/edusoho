<?php
namespace Biz\Activity\Service\Impl;

use Biz\BaseService;
use Biz\Activity\Service\TestpaperActivityService;

class TestpaperActivityServiceImpl extends BaseService implements TestpaperActivityService
{
    public function getActivity($id)
    {
        return $this->getTestpaperActivityDao()->get($id);
    }

    public function findActivitiesByIds($ids)
    {
        return $this->getTestpaperActivityDao()->findActivitiesByIds($ids);
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

    protected function getTestpaperActivityDao()
    {
        return $this->createDao('Activity:TestpaperActivityDao');
    }
}
