<?php

namespace Biz\MultiClass\Service\Impl;

use Biz\BaseService;
use Biz\MultiClass\Dao\MultiClassGroupDao;
use Biz\MultiClass\Service\MultiClassGroupService;

class MultiClassGroupServiceImpl extends BaseService implements MultiClassGroupService
{
    public function findGroupsByIds($ids)
    {
        return $this->getMultiClassGroupDao()->findByIds($ids);
    }

    public function findGroupsByMultiClassId($multiClassId)
    {
        return $this->getMultiClassGroupDao()->findGroupsByMultiClassId($multiClassId);
    }

    public function getById($id)
    {
        return $this->getMultiClassGroupDao()->get($id);
    }

    /**
     * @return MultiClassGroupDao
     */
    protected function getMultiClassGroupDao()
    {
        return $this->createDao('MultiClass:MultiClassGroupDao');
    }
}
