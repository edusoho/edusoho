<?php

namespace Biz\MultiClass\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Assistant\Dao\AssistantStudentDao;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Dao\MultiClassGroupDao;
use Biz\MultiClass\Service\MultiClassGroupService;

class MultiClassGroupServiceImpl extends BaseService implements MultiClassGroupService
{
    const MULTI_CLASS_GROUP_NAME = '分组';

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
    }

    public function getMultiClassGroup($id)
    {
        return $this->getMultiClassGroupDao()->get($id);
    }

    public function createMultiClassGroups($courseId, $multiClass)
    {
        if ('group' != $multiClass['type'] || empty($multiClass['group_limit_num'])) {
            return;
        }

        $roleGroupMemberUserIds = $this->getCourseMemberService()->findGroupUserIdsByCourseIdAndRoles($courseId, ['student', 'assistant']);
        $studentIds = empty($roleGroupMemberUserIds['student']) ? [] : $roleGroupMemberUserIds['student'];
        $groupNum = ceil(count($studentIds) / $multiClass['group_limit_num']);

        $groupAssignStudentIds = [];
        $assignedNum = 0;
        for ($assignedTimes = 0; $assignedTimes <= $groupNum; ++$assignedTimes) {
            $groupAssignStudentIds[$assignedNum] = array_slice($studentIds, $assignedNum, $groupNum);
            $assignedNum += count($groupAssignStudentIds[$assignedNum]);
        }

        $groupSerialNum = 0;
        foreach ($groupAssignStudentIds as $assignStudentIds) {
            ++$groupSerialNum;
            $field['student_num'] = count($assignStudentIds);
            $field['name'] = self::MULTI_CLASS_GROUP_NAME.$groupSerialNum;
            $field['course_id'] = $courseId;
            $field['multi_class_id'] = $multiClass['id'];

            $multiClassGroup = $this->getMultiClassGroupDao()->create($field);

            $studentFields = [];
            foreach ($assignStudentIds as $assignStudentId) {
                $studentField['studentId'] = $assignStudentId;
                $studentField['courseId'] = $courseId;
                $studentField['multiClassId'] = $multiClass['id'];
                $studentField['group_id'] = $multiClassGroup['id'];
                $studentFields[] = $studentField;
            }
            $this->getAssistantStudentDao()->batchCreate($studentFields);
        }

        return true;
    }

    /**
     * @return MultiClassGroupDao
     */
    protected function getMultiClassGroupDao()
    {
        return $this->createDao('MultiClass:MultiClassGroupDao');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return AssistantStudentDao
     */
    protected function getAssistantStudentDao()
    {
        return $this->createDao('Assistant:AssistantStudentDao');
    }

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
