<?php

namespace Biz\Assistant\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Assistant\AssistantException;
use Biz\Assistant\Dao\AssistantStudentDao;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Dao\MultiClassGroupDao;
use Biz\MultiClass\Dao\MultiClassRecordDao;
use Biz\MultiClass\Service\MultiClassRecordService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\System\Service\LogService;

class AssistantStudentServiceImpl extends BaseService implements AssistantStudentService
{
    public function create($fields)
    {
        if (!ArrayToolkit::requireds($fields, ['multiClassId', 'studentId', 'assistantId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $fields = $this->filterAssistantStudentFields($fields);

        return $this->getAssistantStudentDao()->create($fields);
    }

    public function update($id, $fields)
    {
        $fields = $this->filterAssistantStudentFields($fields);

        return $this->getAssistantStudentDao()->update($id, $fields);
    }

    public function get($id)
    {
        return $this->getAssistantStudentDao()->get($id);
    }

    public function delete($id)
    {
        $assistantStudent = $this->get($id);
        if (empty($assistantStudent)) {
            $this->createNewException(AssistantException::ASSISTANT_STUDENT_NOT_FOUND());
        }

        return $this->getAssistantStudentDao()->delete($id);
    }

    protected function filterAssistantStudentFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            [
                'courseId',
                'studentId',
                'assistantId',
                'multiClassId',
            ]
        );
    }

    public function getByStudentIdAndMultiClassId($studentId, $multiClassId)
    {
        return $this->getAssistantStudentDao()->getByStudentIdAndMultiClassId($studentId, $multiClassId);
    }

    public function getByStudentIdAndCourseId($studentId, $courseId)
    {
        return $this->getAssistantStudentDao()->getByStudentIdAndCourseId($studentId, $courseId);
    }

    public function findByStudentIdsAndMultiClassId($studentIds, $multiClassId)
    {
        return $this->getAssistantStudentDao()->findByStudentIdsAndMultiClassId($studentIds, $multiClassId);
    }

    public function findRelationsByAssistantIdAndCourseId($assistantId, $courseId)
    {
        return $this->getAssistantStudentDao()->findByAssistantIdAndCourseId($assistantId, $courseId);
    }

    public function findRelationsByMultiClassIdAndStudentIds($multiClassId, $studentIds)
    {
        return $this->getAssistantStudentDao()->findByMultiClassIdAndStudentIds($multiClassId, $studentIds);
    }

    public function setAssistantStudents($courseId, $multiClassId)
    {
        if (empty($multiClassId) || empty($courseId)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $roleGroupMemberUserIds = $this->getCourseMemberService()->findGroupUserIdsByCourseIdAndRoles($courseId, ['student', 'assistant']);
        $studentIds = empty($roleGroupMemberUserIds['student']) ? [] : ArrayToolkit::column($roleGroupMemberUserIds['student'], 'userId');
        $assistantIds = empty($roleGroupMemberUserIds['assistant']) ? [] : ArrayToolkit::column($roleGroupMemberUserIds['assistant'], 'userId');

        if (empty($assistantIds) || empty($studentIds)) {
            return;
        }

        $multiClassAssistants = $this->getAssistantStudentDao()->findByMultiClassId($multiClassId);
        $existAssistantIds = ArrayToolkit::column($multiClassAssistants, 'assistantId');
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);

        $deleteAssistantIds = array_diff($existAssistantIds, $assistantIds);
        if (!empty($deleteAssistantIds)) {
            $this->getAssistantStudentDao()->batchDelete(['assistantIds' => $deleteAssistantIds]);
            $this->handleMultiClassGroupAssistants($multiClass, $deleteAssistantIds);
        }

        $students = $this->getAssistantStudentDao()->findByMultiClassId($multiClassId);
        $noAssistantStudentIds = array_diff($studentIds, ArrayToolkit::column($students, 'studentId'));

        $studentNumGroup = $this->getAssistantStudentDao()->countMultiClassGroupStudent($multiClassId);
        $studentNumGroup = ArrayToolkit::index($studentNumGroup, 'assistantId');

        $data = $result = [];
        if ('group' == $multiClass['type']) {
            $this->assignGroups($multiClass, $assistantIds);
        } else {
            $this->assignStudents($data, $noAssistantStudentIds, $assistantIds, $studentNumGroup, $multiClass['service_num']);
            foreach ($data as $assistantId => $studentIds) {
                $fields = [];
                foreach ($studentIds as $studentId) {
                    $field['assistantId'] = $assistantId;
                    $field['studentId'] = $studentId;
                    $field['courseId'] = $courseId;
                    $field['multiClassId'] = $multiClassId;
                    $fields[] = $field;
                }
                $this->getAssistantStudentDao()->batchCreate($fields);
                $result[] = $fields;
            }
        }

        if (!empty($result)) {
            $this->getLogService()->info('multi_class_assistant', 'update_assistant_student', '助教学员变更', $result);
        }

        return true;
    }

    private function assignGroups($multiClass, $assistantIds)
    {
        $multiClassGroupNumGroups = $this->getMultiClassGroupDao()->countMultiClassGroupAssistant($multiClass['id']);
        $multiClassGroups = $this->getMultiClassGroupDao()->findGroupsByMultiClassId($multiClass['id']);

        $assistantGroupNum = ceil($multiClass['service_group_num'] / count($assistantIds));
        $data = [];
        $this->assignGroup($data, $assistantIds, ArrayToolkit::column($multiClassGroups, 'id'), $multiClassGroupNumGroups, $assistantGroupNum);
        foreach ($data as $assistantId => $groupIds) {
            foreach ($groupIds as $groupId) {
                $this->getMultiClassGroupDao()->update($groupId, ['assistant_id' => $assistantId]);
            }
        }
    }

    private function handleMultiClassGroupAssistants($multiClass, $deleteAssistantIds)
    {
        if ('group' != $multiClass['type']) {
            return;
        }

        $multiClassGroups = $this->getMultiClassGroupDao()->findGroupsByMultiClassIdAndAssistantIds($multiClass['id'], $deleteAssistantIds);
        foreach ($multiClassGroups as $multiClassGroup) {
            $this->getMultiClassGroupDao()->update($multiClassGroup['id'], ['assistant_id' => 0]);
        }
    }

    private function assignGroup(&$data, $assistantIds, $groupIds, $multiClassGroupNumGroup, $assistantGroupNum, $remaining = false)
    {
        foreach ($assistantIds as $assistantId) {
            $group = empty($multiClassGroupNumGroup[$assistantId]) ? ['groupNum' => 0] : $multiClassGroupNumGroup[$assistantId];

            if ($remaining) {
                $data[$assistantId] = array_merge($data[$assistantId] ?: [], array_slice($groupIds, 0, $assistantGroupNum));
                $assistantIds = array_diff($groupIds, $data[$assistantId]);
                continue;
            }

            if ($group['groupNum'] >= $assistantGroupNum) {
                continue;
            }

            $needGroupNum = $assistantGroupNum - $group['groupNum'];
            $data[$assistantId] = array_slice($groupIds, 0, $needGroupNum);
            $groupIds = array_diff($groupIds, $data[$assistantId]);
        }

        if (!empty($groupIds)) {
            $this->assignGroup($data, $assistantIds, $groupIds, $multiClassGroupNumGroup, 1, true);
        }
    }

    private function assignStudents(&$data, $studentIds, $assistantIds, $studentNumGroup, $average, $remaining = false)
    {
        foreach ($assistantIds as $assistantId) {
            $assistant = empty($studentNumGroup[$assistantId]) ? ['studentNum' => 0] : $studentNumGroup[$assistantId];

            if ($remaining) {
                $data[$assistantId] = array_merge($data[$assistantId] ?: [], array_slice($studentIds, 0, $average));
                $studentIds = array_diff($studentIds, $data[$assistantId]);
                continue;
            }

            if ($assistant['studentNum'] >= $average) {
                continue;
            }

            $needAssignNum = $average - $assistant['studentNum'];
            $data[$assistantId] = array_slice($studentIds, 0, $needAssignNum);
            $studentIds = array_diff($studentIds, $data[$assistantId]);
        }

        if (!empty($studentIds)) {
            $this->assignStudents($data, $studentIds, $assistantIds, $studentNumGroup, 1, true);
        }
    }

    public function deleteByStudentIdAndCourseId($studentId, $courseId)
    {
        return $this->getAssistantStudentDao()->deleteByStudentIdAndCourseId($studentId, $courseId);
    }

    public function findByMultiClassId($multiClassId)
    {
        return $this->getAssistantStudentDao()->findByMultiClassId($multiClassId);
    }

    public function findByMultiClassIds($multiClassIds)
    {
        return $this->getAssistantStudentDao()->findByMultiClassIds($multiClassIds);
    }

    public function filterAssistantConditions($conditions, $courseId)
    {
        $user = $this->getCurrentUser();
        $member = $this->getCourseMemberService()->getCourseMember($courseId, $user['id']);
        if ('assistant' != $member['role']) {
            return $conditions;
        }

        $assistantStudentRef = $this->findRelationsByAssistantIdAndCourseId($user['id'], $courseId);
        if (empty($assistantStudentRef)) {
            $conditions['userIds'] = [-1];

            return $conditions;
        }

        $studentIds = ArrayToolkit::column($assistantStudentRef, 'studentId');
        $conditions['userIds'] = !empty($conditions['userIds']) ? array_intersect($studentIds, $conditions['userIds']) : $studentIds;

        return $conditions;
    }

    public function batchUpdateStudentsGroup($multiClassId, $studentIds, $groupId)
    {
        try {
            $this->beginTransaction();

            $originRelations = $this->findByStudentIdsAndMultiClassId($studentIds, $multiClassId);
            $originRelations = ArrayToolkit::index($originRelations, 'studentId');
            $this->getAssistantStudentDao()->updateMultiClassStudentsGroup($multiClassId, ['groupId' => $groupId, 'studentIds' => $studentIds]);
            $this->batchCreateRecords($multiClassId, $studentIds, $originRelations);
            $this->batchUpdateGroupStudentNum($multiClassId, array_merge([$groupId], ArrayToolkit::column($originRelations, 'group_id')));

            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchUpdateStudentsGroup:'.$e->getMessage(), ['multiClassId' => $multiClassId, 'studentIds' => $studentIds, 'groupId' => $groupId]);
            $this->rollback();
            throw $e;
        }
    }

    private function batchUpdateGroupStudentNum($multiClassId, $groupIds)
    {
        $groupIds = array_values(array_unique($groupIds));
        $groupStudentNum = $this->getAssistantStudentDao()->countMultiClassGroupStudentByGroupIds($multiClassId, $groupIds);
        $groupStudentNum = ArrayToolkit::index($groupStudentNum, 'groupId');

        $updateRecords = [];
        foreach ($groupIds as $groupId) {
            $updateRecords[] = ['student_num' => $groupStudentNum[$groupId]['studentNum'] ?: 0];
        }

        return $this->getMultiClassGroupDao()->batchUpdate($groupIds, $updateRecords);
    }

    private function batchCreateRecords($multiClassId, $studentIds, $originRelations)
    {
        $currentRelations = $this->findByStudentIdsAndMultiClassId($studentIds, $multiClassId);
        $currentRelations = ArrayToolkit::index($currentRelations, 'studentId');

        $records = [];
        foreach ($studentIds as $studentId) {
            $records[] = [
                'user_id' => $studentId,
                'multi_class_id' => $multiClassId,
                'data' => json_encode(['title' => '变更分组', 'message' => sprintf('原分组id：%s, 变更后分组id：%s', $originRelations[$studentId]['group_id'], $currentRelations[$studentId]['group_id'])]),
                'sign' => $this->getMultiClassRecordService()->makeSign(),
            ];
        }

        return $this->getMultiClassRecordDao()->batchCreate($records);
    }

    /**
     * @return MultiClassGroupDao
     */
    private function getMultiClassGroupDao()
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

    public function findByMultiClassIdAndGroupId($multiClassId, $groupId)
    {
        return $this->getAssistantStudentDao()->findByMultiClassIdAndGroupId($multiClassId, $groupId);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return AssistantStudentDao
     */
    protected function getAssistantStudentDao()
    {
        return $this->createDao('Assistant:AssistantStudentDao');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->createService('MultiClass:MultiClassService');
    }
}
