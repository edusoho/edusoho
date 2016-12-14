<?php
namespace Biz\TestpaperActivity\Service\Impl;

use Biz\BaseService;
use Topxia\Service\Common\ServiceKernel;
use Biz\TestpaperActivity\Service\TestpaperActivityService;

class TestpaperActivityServiceImpl extends BaseService implements TestpaperActivityService
{
    public function getActivity($id)
    {
        return $this->getTestpaperActivityDao()->get($id);
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
        return $this->createDao('TestpaperActivity:TestpaperActivityDao');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
