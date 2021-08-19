<?php

namespace Biz\MultiClass\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\MultiClass\Dao\MultiClassGroupDao;
use Biz\MultiClass\Dao\MultiClassLiveGroupDao;
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

    public function findGroupsByCourseId($courseId)
    {
        return $this->getMultiClassGroupDao()->findByCourseId($courseId);
    }

    public function getLiveGroupByUserIdAndCourseId($userId, $courseId, $liveId)
    {
        $assistantRef = $this->getAssistantStudentService()->getByStudentIdAndCourseId($userId, $courseId);
        if (empty($assistantRef) || empty($assistantRef['group_id'])) {
            return [];
        }

        $liveGroup = $this->getMultiClassLiveGroupDao()->getByGroupIdAndLiveId($assistantRef['group_id'], $liveId);
        if (empty($liveGroup) || empty($liveGroup['live_code'])) {
            return [];
        }

        return $liveGroup;
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

    /**
     * @return MultiClassLiveGroupDao
     */
    protected function getMultiClassLiveGroupDao()
    {
        return $this->createDao('MultiClass:MultiClassLiveGroupDao');
    }

    /**
     * @return AssistantStudentService
     */
    protected function getAssistantStudentService()
    {
        return $this->createDao('MultiClass:MultiClassLiveGroupDao');
    }
}
