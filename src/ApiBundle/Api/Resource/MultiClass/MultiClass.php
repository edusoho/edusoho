<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassProductService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;

class MultiClass extends AbstractResource
{
    const MAX_ASSISTANT_NUMBER = 20;

    public function get(ApiRequest $request)
    {
        return [];
    }

    public function add(ApiRequest $request)
    {
        $multiClass = $this->checkDataFields($request->request->all());

        $existed = $this->getMultiClassService()->getMultiClassByTitle($multiClass['title']);

        if ($existed) {
            throw MultiClassException::MULTI_CLASS_EXIST();
        }

        return $this->getMultiClassService()->createMultiClass($multiClass);
    }

    public function update(ApiRequest $request, $id)
    {
        $multiClass = $this->checkDataFields($request->request->all());

        $existed = $this->getMultiClassService()->getMultiClassByTitle($multiClass['title']);

        if (!empty($existed) && $id != $existed['id']) {
            throw MultiClassException::MULTI_CLASS_EXIST();
        }

        return $this->getMultiClassService()->updateMultiClass($id, $multiClass);
    }

    public function remove(ApiRequest $request, $id)
    {
        $this->getMultiClassService()->deleteMultiClass($id);

        return ['success' => true];
    }

    public function search(ApiRequest $request)
    {
        $conditions = $this->prepareConditions($request->query->all());
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $multiClasses = $this->getMultiClassService()->searchMultiClass($conditions, ['createdTime' => 'DESC'], $offset, $limit);
        $multiClassesCount = $this->getMultiClassService()->countMultiClass($conditions);
        $multiClasses = $this->makeMultiClassesInfo($multiClasses);

        return $this->makePagingObject($multiClasses, $multiClassesCount, $offset, $limit);
    }

    private function prepareConditions($conditions)
    {
        $prepareConditions = [];
        if (!empty($conditions['keywords'])) {
            $courses = $this->getCourSetService()->findCourseLikeCourseSetTitle($conditions['keywords']);
            $prepareConditions['courseIds'] = ArrayToolkit::column($courses, 'id');
            $userIds = ArrayToolkit::column($this->getUserService()->findUserLikeNickname($conditions['keywords']), 'id');
            $userIds = empty($userIds) ? [-1] : $userIds;
            $prepareConditions['ids'] = $this->getCourseMemberService()->searchMultiClassIds([
                'userIds' => $userIds,
                'role' => 'teacher', ],
                [], 0, PHP_INT_MAX
            );
        }

        return $prepareConditions;
    }

    private function makeMultiClassesInfo($multiClasses)
    {
        $multiClassIds = ArrayToolkit::column($multiClasses, 'id');
        $courseIds = ArrayToolkit::column($multiClasses, 'courseId');
        $productIds = ArrayToolkit::column($multiClasses, 'productId');

        $teachers = $this->getCourseMemberService()->findMultiClassTeachersByMultiClassIds($multiClassIds);
        $assistants = $this->getCourseMemberService()->findMultiClassAssistantByMultiClassIds($multiClassIds);
        $userIds = array_values(array_unique(array_merge(
            ArrayToolkit::column($teachers, 'userId'),
            ArrayToolkit::column($assistants, 'userId'))));
        $teachersIndexByMultiClassId = ArrayToolkit::index($teachers, 'multiClassId');
        $assistantGroupByMultiClassId = ArrayToolkit::group($assistants, 'multiClassId');

        $courses = $this->getCourSetService()->findCoursesByIds($courseIds);
        $users = $this->getUserService()->findUsersByIds($userIds);
        $products = $this->getMultiClassProductService()->findProductByIds($productIds);

        foreach ($multiClasses as &$multiClass) {
            $teacher = $teachersIndexByMultiClassId[$multiClass['id']];
            $assistantIds = ArrayToolkit::column($assistantGroupByMultiClassId[$multiClass['id']], 'userId');
            $multiClass['course'] = $courses[$multiClass['courseId']]['courseSetTitle'];
            $multiClass['product'] = $products[$multiClass['productId']]['title'];
            $multiClass['price'] = $courses[$multiClass['courseId']]['price'];
            $multiClass['taskNum'] = $this->getTaskService()->countTasks(['multiClassId' => $multiClass['id'], 'status' => 'published', 'isLesson' => 1]);
            $multiClass['notStartLiveTaskNum'] = $this->getTaskService()->countTasks(['multiClassId' => $multiClass['id'], 'status' => 'published', 'isLesson' => 1, 'startTime_GT' => time()]);

            $multiClass['teacherId'] = $users[$teacher['userId']]['id'];
            $multiClass['teacher'] = $users[$teacher['userId']]['nickname'];
            $multiClass['assistantIds'] = $assistantIds;
            $multiClass['assistant'] = [];
            array_walk($assistantIds, function ($id) use (&$multiClass,$users) {
                $multiClass['assistant'][] = $users[$id]['nickname'];
            });
            $multiClass['studentNum'] = $this->getCourseMemberService()->countMembers(['multiClassId' => $multiClass['id'], 'role' => 'student']);
        }

        return $multiClasses;
    }

    private function checkDataFields($multiClass)
    {
        if (!ArrayToolkit::requireds($multiClass, ['title', 'courseId', 'productId'])) {
            throw MultiClassException::MULTI_CLASS_DATA_FIELDS_MISSING();
        }

        if (empty($multiClass['teacherId'])) {
            throw MultiClassException::MULTI_CLASS_TEACHER_REQUIRE();
        }

        if (empty($multiClass['assistantIds'])) {
            throw MultiClassException::MULTI_CLASS_ASSISTANT_REQUIRE();
        }

        if (count($multiClass['assistantIds']) > self::MAX_ASSISTANT_NUMBER) {
            throw MultiClassException::MULTI_CLASS_ASSISTANT_NUMBER_EXCEED();
        }

        return $multiClass;
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return CourseService
     */
    protected function getCourSetService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return MultiClassProductService
     */
    protected function getMultiClassProductService()
    {
        return $this->service('MultiClass:MultiClassProductService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }
}
