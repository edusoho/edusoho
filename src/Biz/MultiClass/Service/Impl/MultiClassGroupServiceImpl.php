<?php

namespace Biz\MultiClass\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\MultiClass\Dao\MultiClassGroupDao;
use Biz\MultiClass\Dao\MultiClassLiveGroupDao;
use Biz\MultiClass\Service\MultiClassGroupService;

class MultiClassGroupServiceImpl extends BaseService implements MultiClassGroupService
{
    public function findGroupsByMultiClassId($multiClassId)
    {
        return $this->getMultiClassGroupDao()->findGroupsByMultiClassId($multiClassId);
    }

    public function findGroupsByCourseId($courseId)
    {
        return $this->getMultiClassGroupDao()->findByCourseId($courseId);
    }

    public function createLiveGroup($fields)
    {
        if (!ArrayToolkit::requireds($fields, ['group_id', 'live_code', 'live_id'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $fields = ArrayToolkit::parts($fields, ['group_id', 'live_code', 'live_id']);

        return $this->getMultiClassLiveGroupDao()->create($fields);
    }

    public function batchCreateLiveGroups($liveGroups)
    {
        if (empty($liveGroups)) {
            return;
        }

        $this->getMultiClassLiveGroupDao()->batchCreate($liveGroups);
    }

    /**
     * @return MultiClassGroupDao
     */
    protected function getMultiClassGroupDao()
    {
        return $this->createDao('MultiClass:MultiClassGroupDao');
    }

    /**
     * @return MultiClassLiveGroupDao
     */
    protected function getMultiClassLiveGroupDao()
    {
        return $this->createDao('MultiClass:MultiClassLiveGroupDao');
    }
}
