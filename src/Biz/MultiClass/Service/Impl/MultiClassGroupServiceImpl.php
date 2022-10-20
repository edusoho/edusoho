<?php

namespace Biz\MultiClass\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Assistant\Dao\AssistantStudentDao;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Dao\MultiClassGroupDao;
use Biz\MultiClass\Dao\MultiClassLiveGroupDao;
use Biz\MultiClass\Dao\MultiClassRecordDao;
use Biz\MultiClass\Service\MultiClassGroupService;
use Biz\MultiClass\Service\MultiClassRecordService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;

class MultiClassGroupServiceImpl extends BaseService implements MultiClassGroupService
{
    public function findGroupsByIds($ids)
    {
        return ArrayToolkit::index($this->getMultiClassGroupDao()->findByIds($ids), 'id');
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
        $multiClass = $this->getMultiClassService()->getMultiClassByCourseId($courseId);
        if (empty($multiClass)) {
            return [];
        }
        $assistantRef = $this->getAssistantStudentService()->getByStudentIdAndMultiClassId($userId, $multiClass['id']);
        if (empty($assistantRef) || empty($assistantRef['group_id'])) {
            return [];
        }

        $liveGroup = $this->getMultiClassLiveGroupDao()->getByGroupId($assistantRef['group_id']);
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

    public function deleteMultiClassGroup($id)
    {
        $result = $this->getMultiClassGroupDao()->delete($id);
        $liveGroup = $this->getMultiClassLiveGroupDao()->getByGroupId($id);
        $this->getMultiClassLiveGroupDao()->delete($liveGroup['id']);
        $this->dispatchEvent('multi_class.group_delete', $liveGroup);

        return $result;
    }

    public function sortMultiClassGroup($multiClassId)
    {
        $groups = $this->getMultiClassGroupDao()->findMultiClassGroupsByMultiClassId($multiClassId);

        if (empty($groups)) {
            return;
        }

        $ids = ArrayToolkit::column($groups, 'id');
        foreach ($ids as $index => $id) {
            $this->getMultiClassGroupDao()->update($id, ['seq' => $index + 1]);
        }
    }

    public function updateMultiClassGroup($id, $fields)
    {
        return $this->getMultiClassGroupDao()->update($id, $fields);
    }

    public function getLatestGroup($multiClassId)
    {
        return $this->getMultiClassGroupDao()->getLatestGroup($multiClassId);
    }

    public function batchUpdateGroupAssistant($multiClassId, $groupIds, $assistantId)
    {
        try {
            $this->beginTransaction();

            $groups = $this->findGroupsByIds($groupIds);
            $groupFields = [];
            foreach ($groups as $group) {
                $groupFields[] = [
                    'id' => $group['id'],
                    'assistant_id' => $assistantId,
                ];
            }
            $this->getMultiClassGroupDao()->batchUpdate(ArrayToolkit::column($groups, 'id'), $groupFields);

            $assistantStudents = $this->getAssistantStudentService()->findAssistantStudentsByGroupIds($groupIds);
            $assistantFields = [];
            foreach ($assistantStudents as $assistantStudent) {
                $assistantFields[] = [
                    'id' => $assistantStudent['id'],
                    'assistantId' => $assistantId,
                ];
            }
            $this->getAssistantStudentDao()->batchUpdate(ArrayToolkit::column($assistantStudents, 'id'), $assistantFields);

            $this->batchCreateRecords($multiClassId, $groups, $assistantId, $assistantStudents);

            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchUpdateGroupAssistant:'.$e->getMessage(), ['multiClassId' => $multiClassId, 'groupIds' => $groupIds, 'assistantId' => $assistantId]);
            $this->rollback();
            throw $e;
        }

        return true;
    }

    public function batchDeleteMultiClassGroups($ids)
    {
        if (empty($ids)) {
            return;
        }
        $this->getMultiClassGroupDao()->batchDelete(['ids' => $ids]);
        $liveGroups = $this->getMultiClassLiveGroupDao()->findByGroupIds($ids);
        $this->getMultiClassLiveGroupDao()->batchDelete(['ids' => array_column($liveGroups, 'id')]);

        $this->dispatchEvent('multi_class.group_batch_delete', $liveGroups);
    }

    private function batchCreateRecords($multiClassId, $groups, $assistantId, $assistantStudents)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        $assistant = $this->getUserService()->getUser($assistantId);

        $records = [];
        foreach ($assistantStudents as $assistantStudent) {
            $group = $groups[$assistantStudent['group_id']];
            $content = sprintf('加入班课(%s)的%s, 分配助教(%s)', $multiClass['title'], MultiClassGroupService::MULTI_CLASS_GROUP_NAME.$group['seq'], $assistant['nickname']);
            $records[] = [
                'user_id' => $assistantStudent['studentId'],
                'assistant_id' => $assistantId,
                'multi_class_id' => $multiClassId,
                'data' => ['title' => '加入班课', 'content' => $content],
                'sign' => $this->getMultiClassRecordService()->makeSign(),
                'is_push' => 0,
            ];
        }

        return $this->getMultiClassRecordDao()->batchCreate($records);
    }

    public function setGroupNewStudent($multiClass, $studentId)
    {
        if ('group' != $multiClass['type'] || empty($multiClass['group_limit_num'])) {
            return;
        }

        $noFullGroup = $this->getMultiClassGroupDao()->getNoFullGroup($multiClass['id'], $multiClass['group_limit_num']);
        if (empty($noFullGroup)) {
            $field = [];
            $latestGroup = $this->getLatestGroup($multiClass['id']);
            $field['name'] = empty($latestGroup) ? self::MULTI_CLASS_GROUP_NAME.'1' : self::MULTI_CLASS_GROUP_NAME.($latestGroup['seq'] + 1);
            $field['seq'] = empty($latestGroup) ? 1 : $latestGroup['seq'] + 1;
            $field['multi_class_id'] = $multiClass['id'];
            $field['course_id'] = $multiClass['courseId'];
            $field['student_num'] = 1;
            $field['assistant_id'] = 0;
            $group = $this->getMultiClassGroupDao()->create($field);

            $this->dispatchEvent('multi_class.group_create', new Event($multiClass, ['groups' => [$group]]));
        } else {
            $group = $this->getMultiClassGroupDao()->update($noFullGroup['id'], ['student_num' => $noFullGroup['student_num'] + 1]);
        }
        $studentField = [];
        $studentField['studentId'] = $studentId;
        $studentField['courseId'] = $multiClass['courseId'];
        $studentField['multiClassId'] = $multiClass['id'];
        $studentField['group_id'] = $group['id'];
        if (!empty($group)) {
            $studentField['assistantId'] = $group['assistant_id'];
        }
        $this->getAssistantStudentDao()->create($studentField);

        $this->getAssistantStudentService()->setGroupAssistantAndStudents($multiClass['courseId'], $multiClass['id']);

        return true;
    }

    public function createMultiClassGroups($courseId, $multiClass)
    {
        if ('group' != $multiClass['type'] || empty($multiClass['group_limit_num'])) {
            return;
        }

        $roleGroupMemberUserIds = $this->getCourseMemberService()->findGroupUserIdsByCourseIdAndRoles($courseId, ['student', 'assistant']);
        $studentIds = empty($roleGroupMemberUserIds['student']) ? [] : ArrayToolkit::column($roleGroupMemberUserIds['student'], 'userId');
        $groupNum = ceil(count($studentIds) / $multiClass['group_limit_num']);

        $groupAssignStudentIds = [];
        $assignedNum = 0;
        for ($assignedTimes = 0; $assignedTimes < $groupNum; ++$assignedTimes) {
            $groupAssignStudentIds[$assignedNum] = array_slice($studentIds, $assignedNum, $multiClass['group_limit_num']);
            $assignedNum += count($groupAssignStudentIds[$assignedNum]);
        }

        $groupSeqNum = 0;
        $groups = [];
        foreach ($groupAssignStudentIds as $assignStudentIds) {
            ++$groupSeqNum;
            $field['student_num'] = count($assignStudentIds);
            $field['name'] = self::MULTI_CLASS_GROUP_NAME.$groupSeqNum;
            $field['seq'] = $groupSeqNum;
            $field['course_id'] = $courseId;
            $field['multi_class_id'] = $multiClass['id'];
            $field['assistant_id'] = 0;

            $groups[] = $multiClassGroup = $this->getMultiClassGroupDao()->create($field);

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

        $this->dispatchEvent('multi_class.group_create', new Event($multiClass, ['groups' => $groups]));

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
     * @return MultiClassRecordDao
     */
    private function getMultiClassRecordDao()
    {
        return $this->createDao('MultiClass:MultiClassRecordDao');
    }

    /**
     * @return MultiClassRecordService
     */
    private function getMultiClassRecordService()
    {
        return $this->createService('MultiClass:MultiClassRecordService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->createService('MultiClass:MultiClassService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
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
        return $this->createService('Assistant:AssistantStudentService');
    }
}
