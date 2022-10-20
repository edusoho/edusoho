<?php

namespace Biz\MultiClass\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Assistant\Service\AssistantStudentService;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Dao\MultiClassDao;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassGroupService;
use Biz\MultiClass\Service\MultiClassProductService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\System\Service\CacheService;
use Biz\System\Service\LogService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use ESCloud\SDK\Service\ScrmService;

class MultiClassServiceImpl extends BaseService implements MultiClassService
{
    public function findAllMultiClass()
    {
        return $this->getMultiClassDao()->findAll();
    }

    public function findByProductIds(array $productIds)
    {
        return $this->getMultiClassDao()->findByProductIds($productIds);
    }

    public function findMultiClassesByCourseIds($courseIds)
    {
        return $this->getMultiClassDao()->findByCourseIds($courseIds);
    }

    public function findByProductId($productId)
    {
        return $this->getMultiClassDao()->findByProductId($productId);
    }

    public function findMultiClassesByCreator($creator)
    {
        return $this->getMultiClassDao()->findByCreator($creator);
    }

    public function findMultiClassesByReplayShow($isReplayShow)
    {
        return $this->getMultiClassDao()->findByReplayShow($isReplayShow);
    }

    public function getMultiClass($id)
    {
        return $this->getMultiClassDao()->get($id);
    }

    public function countMultiClassByCopyId($id)
    {
        return $this->getMultiClassDao()->count(['copyId' => $id]);
    }

    public function createMultiClass($fields)
    {
        if (!$this->canCreateMultiClass()) {
            throw MultiClassException::CAN_NOT_MANAGE_MULTI_CLASS();
        }

        $teacherId = [['id' => $fields['teacherId'], 'isVisible' => 1]];
        $assistantIds = $fields['assistantIds'];

        $fields = $this->filterMultiClassFields($fields);
        if (!ArrayToolkit::requireds($fields, ['title', 'courseId', 'productId', 'maxStudentNum', 'isReplayShow', 'type'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $this->beginTransaction();
        try {
            $fields['creator'] = $this->getCurrentUser()->getId();
            $multiClass = $this->getMultiClassDao()->create($fields);
            $this->getCourseMemberService()->setCourseTeachers($fields['courseId'], $teacherId, $multiClass['id']);
            $this->getCourseMemberService()->setCourseAssistants($fields['courseId'], $assistantIds, $multiClass['id']);
            if ('group' == $multiClass['type']) {
                $this->getMultiClassGroupService()->createMultiClassGroups($fields['courseId'], $multiClass);
                $this->getAssistantStudentService()->setGroupAssistantAndStudents($fields['courseId'], $multiClass['id']);
            } else {
                $this->getAssistantStudentService()->setAssistantStudents($fields['courseId'], $multiClass['id']);
            }
            $this->generateMultiClassTimeRange($fields['courseId']);

            $this->getLogService()->info(
                'multi_class',
                'create_multi_class',
                "创建班课#{$multiClass['id']}《{$multiClass['title']}》",
                $multiClass
            );

            $this->dispatchEvent('multi_class.create', new Event($multiClass));

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $multiClass;
    }

    public function updateMultiClass($id, $fields)
    {
        $multiClassExisted = $this->getMultiClassDao()->get($id);
        if (empty($multiClassExisted)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        if (!$this->canManageMultiClass($id, 'multi_class_edit')) {
            throw MultiClassException::CAN_NOT_MANAGE_MULTI_CLASS();
        }

        $teacherId = [['id' => $fields['teacherId'], 'isVisible' => 1]];
        $assistantIds = $fields['assistantIds'];

        $fields = $this->filterMultiClassFields($fields, $id);

        $this->beginTransaction();
        try {
            $multiClass = $this->getMultiClassDao()->update($id, $fields);
            $this->getCourseMemberService()->setCourseTeachers($fields['courseId'], $teacherId, $multiClass['id']);
            $this->getCourseMemberService()->setCourseAssistants($fields['courseId'], $assistantIds, $multiClass['id']);
            if ('group' == $multiClass['type']) {
                $this->getAssistantStudentService()->setGroupAssistantAndStudents($fields['courseId'], $multiClass['id']);
            } else {
                $this->getAssistantStudentService()->setAssistantStudents($fields['courseId'], $multiClass['id']);
            }

            $this->getLogService()->info(
                'multi_class',
                'update_multi_class',
                "更新班课#{$multiClass['id']}《{$fields['title']}》",
                $fields
            );

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $multiClass;
    }

    public function deleteMultiClass($id)
    {
        $multiClassExisted = $this->getMultiClassDao()->get($id);
        if (empty($multiClassExisted)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        if (!$this->canManageMultiClass($id, 'multi_class_delete')) {
            throw MultiClassException::CAN_NOT_MANAGE_MULTI_CLASS();
        }

        $this->beginTransaction();
        try {
            $this->getCourseMemberService()->releaseMultiClassMember($multiClassExisted['courseId'], $multiClassExisted['id']);
            $this->getMultiClassDao()->delete($id);
            $this->getAssistantStudentService()->deleteByMultiClassId($id);
            $multiClassGroups = $this->getMultiClassGroupService()->findGroupsByMultiClassId($id);
            $this->getMultiClassGroupService()->batchDeleteMultiClassGroups(array_column($multiClassGroups, 'id'));

            $this->getLogService()->info(
                'multi_class',
                'delete_multi_class',
                "删除班课#{$id}《{$multiClassExisted['title']}》"
            );

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function searchMultiClassJoinCourse($conditions, $orderBys, $start, $limit)
    {
        $conditions = $this->filterConditions($conditions);

        return $this->getMultiClassDao()->searchMultiClassJoinCourse($conditions, $orderBys, $start, $limit);
    }

    public function searchUserTeachMultiClass($userId, $conditions, $start, $limit)
    {
        $multiClassIds = $this->findUserTeachMultiClassIds($userId);
        if (empty($multiClassIds)) {
            return [];
        }

        $conditions['ids'] = $multiClassIds;

        return $this->searchMultiClass($conditions, ['createdTime' => 'desc'], $start, $limit);
    }

    public function countUserTeachMultiClass($userId, $conditions)
    {
        $multiClassIds = $this->findUserTeachMultiClassIds($userId);
        if (empty($multiClassIds)) {
            return 0;
        }

        $conditions['ids'] = $multiClassIds;

        return $this->countMultiClass($conditions);
    }

    protected function findUserTeachMultiClassIds($userId)
    {
        $multiClasses = $this->findMultiClassesByCreator($userId);
        $members = $this->getCourseMemberService()->findMembersByUserIdAndRoles($userId, ['assistant', 'teacher']);
        $multiClassIds = array_merge(ArrayToolkit::column($multiClasses, 'id'), ArrayToolkit::column($members, 'multiClassId'));
        $multiClassIds = array_unique($multiClassIds);

        return array_values($multiClassIds);
    }

    public function searchMultiClass($conditions, $orderBys, $start, $limit, $columns = [])
    {
        $conditions = $this->filterConditions($conditions);

        return $this->getMultiClassDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countMultiClass($conditions)
    {
        $conditions = $this->filterConditions($conditions);

        return $this->getMultiClassDao()->count($conditions);
    }

    public function cloneMultiClass($id, $cloneMultiClass)
    {
        if (!$this->canManageMultiClass($id, 'multi_class_copy')) {
            throw MultiClassException::CAN_NOT_MANAGE_MULTI_CLASS();
        }

        $multiClass = $this->getMultiClassDao()->get($id);
        $newMultiClass = $this->biz['multi_class_copy']->copy($multiClass, [
            'cloneMultiClass' => $cloneMultiClass,
        ]);

        $this->getLogService()->info(
            'multi_class',
            'clone_multi_class',
            "复制班课 - {$multiClass['title']}(#{$id}) 成功",
            ['multiClassId' => $id]);

        return $newMultiClass;
    }

    public function getMultiClassByTitle($title)
    {
        return $this->getMultiClassDao()->getByTitle($title);
    }

    public function canManageMultiClass($multiClassId, $action = '')
    {
        $user = $this->getCurrentUser();
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        $member = $this->getCourseMemberService()->getMemberByMultiClassIdAndUserId($multiClassId, $user['id']);
        if (!empty($member)) {
            if ('teacher' === $member['role']) {
                return true;
            }

            $assistant = $this->biz['assistant_permission'];
            if ('assistant' === $member['role'] && $assistant->hasActionPermission($action)) {
                return true;
            }
        }

        return false;
    }

    public function canCreateMultiClass()
    {
        $user = $this->getCurrentUser();
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        if (in_array('ROLE_TEACHER', $user['roles'])) {
            return true;
        }

        $assistant = $this->biz['assistant_permission'];
        if (in_array('ROLE_TEACHER_ASSISTANT', $user['roles']) && $assistant->hasActionPermission('multi_class_create')) {
            return true;
        }

        return false;
    }

    public function getMultiClassByCourseId($courseId)
    {
        return $this->getMultiClassDao()->getByCourseId($courseId);
    }

    public function generateMultiClassTimeRange($courseId)
    {
        $multiClass = $this->getMultiClassByCourseId($courseId);
        if (empty($multiClass)) {
            return;
        }

        $firstLive = $this->getTaskService()->searchTasks(['courseId' => $courseId, 'type' => 'live'], ['startTime' => 'ASC'], 0, 1);
        $endLive = $this->getTaskService()->searchTasks(['courseId' => $courseId, 'type' => 'live'], ['endTime' => 'DESC'], 0, 1);

        if (!empty($firstLive)) {
            return $this->getMultiClassDao()->update($multiClass['id'], ['start_time' => current($firstLive)['startTime'], 'end_time' => current($endLive)['endTime']]);
        } else {
            return $this->getMultiClassDao()->update($multiClass['id'], ['start_time' => 0, 'end_time' => 0]);
        }
    }

    public function updateMultiClassBundleNo($id, $bundleNo)
    {
        return $this->getMultiClassDao()->update($id, ['bundle_no' => $bundleNo]);
    }

    private function filterConditions($conditions)
    {
        if (isset($conditions['ids']) && empty($conditions['ids'])) {
            $conditions['ids'] = [-1];
        }
        if (isset($conditions['courseIds']) && empty($conditions['courseIds'])) {
            $conditions['courseIds'] = [-1];
        }

        return $conditions;
    }

    private function filterMultiClassFields($fields, $multiClassId = 0)
    {
        if (isset($fields['courseId'])) {
            $course = $this->getCourseService()->getCourse($fields['courseId']);
            if (empty($course)) {
                throw CourseException::NOTFOUND_COURSE();
            }

            $multiClass = $this->getMultiClassByCourseId($course['id']);
            if (!empty($multiClass) && $multiClass['id'] != $multiClassId) {
                throw MultiClassException::MULTI_CLASS_COURSE_EXIST();
            }
        }

        if (isset($fields['productId'])) {
            $course = $this->getMultiClassProductService()->getProduct($fields['productId']);
            if (empty($course)) {
                throw MultiClassException::PRODUCT_NOT_FOUND();
            }
        }

        return ArrayToolkit::parts($fields, ['type', 'title', 'courseId', 'productId', 'maxStudentNum', 'isReplayShow', 'copyId', 'liveRemindTime', 'service_num', 'service_group_num', 'group_limit_num']);
    }

    /**
     * @return ScrmService
     */
    protected function getSCRMService()
    {
        return $this->biz['ESCloudSdk.scrm'];
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return MultiClassProductService
     */
    protected function getMultiClassProductService()
    {
        return $this->createService('MultiClass:MultiClassProductService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return AssistantStudentService
     */
    protected function getAssistantStudentService()
    {
        return $this->createService('Assistant:AssistantStudentService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return MultiClassGroupService
     */
    private function getMultiClassGroupService()
    {
        return $this->createService('MultiClass:MultiClassGroupService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return MultiClassDao
     */
    protected function getMultiClassDao()
    {
        return $this->createDao('MultiClass:MultiClassDao');
    }

    /**
     * @return CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }
}
