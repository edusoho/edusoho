<?php

namespace ApiBundle\Api\Resource\MultiClass;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Assistant\AssistantFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassProductService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;

class MultiClass extends AbstractResource
{
    const MAX_ASSISTANT_NUMBER = 20;

    public function get(ApiRequest $request, $multiClassId)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (empty($multiClass)) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $teachers = $this->getMemberService()->findMultiClassMemberByMultiClassIdAndRole($multiClass['id'], 'teacher');
        $multiClass['teacherIds'] = ArrayToolkit::column($teachers, 'userId');

        $assistants = $this->getMemberService()->findMultiClassMemberByMultiClassIdAndRole($multiClass['id'], 'assistant');
        $multiClass['assistantIds'] = ArrayToolkit::column($assistants, 'userId');

        $this->getOCUtil()->single($multiClass, ['teacherIds', 'assistantIds']);
        $this->getOCUtil()->single($multiClass, ['courseId'], 'course');

        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filters($multiClass['teachers']);

        $assistantFilter = new AssistantFilter();
        $assistantFilter->filters($multiClass['assistants']);

        $product = $this->getMultiClassProductService()->getProduct($multiClass['productId']);
        $multiClass['product'] = empty($product) ? [] : $product;

        return $multiClass;
    }

    public function add(ApiRequest $request)
    {
        $multiClass = $this->checkDataFields($request->request->all());

        $existed = $this->getMultiClassService()->getMultiClassByTitle($multiClass['title']);
        if ($existed) {
            throw MultiClassException::MULTI_CLASS_EXIST();
        }

        $courseExisted = $this->getMultiClassService()->getMultiClassByCourseId($multiClass['courseId']);
        if ($courseExisted) {
            throw MultiClassException::MULTI_CLASS_COURSE_EXIST();
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

        $courseExisted = $this->getMultiClassService()->getMultiClassByCourseId($multiClass['courseId']);
        if ($courseExisted && $id != $courseExisted['id']) {
            throw MultiClassException::MULTI_CLASS_COURSE_EXIST();
        }

        return $this->getMultiClassService()->updateMultiClass($id, $multiClass);
    }

    public function remove(ApiRequest $request, $id)
    {
        $this->getMultiClassService()->deleteMultiClass($id);

        return ['success' => true];
    }

    /**
     * @return array
     */
    public function search(ApiRequest $request)
    {
        $conditions = $this->prepareConditions($request->query->all());
        $orderBys = $this->prepareOrderBys($request->query->all());
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $multiClasses = $this->getMultiClassService()->searchMultiClassJoinCourse($conditions, $orderBys, $offset, $limit);
        $multiClassesCount = $this->getMultiClassService()->countMultiClass($conditions);
        $multiClasses = $this->makeMultiClassesInfo($multiClasses);

        return $this->makePagingObject($multiClasses, $multiClassesCount, $offset, $limit);
    }

    private function prepareConditions($conditions)
    {
        $prepareConditions = [];

        if (!empty($conditions['keywords'])) {
            $prepareConditions['titleLike'] = $conditions['keywords'];
        }

        if (!empty($conditions['productIds'])) {
            $prepareConditions['productIds'] = $conditions['productIds'];
        }

        if (!empty($conditions['productId'])) {
            $prepareConditions['productId'] = $conditions['productId'];
        }

        if (!empty($conditions['status'])) {
            switch ($conditions['status']) {
                case 'notStart':
                    $prepareConditions['startTime_GT'] = time();
                    break;
                case 'living':
                    $prepareConditions['startTime_LE'] = time();
                    $prepareConditions['endTime_GE'] = time();
                    break;
                case 'end':
                    $prepareConditions['endTime_LT'] = time();
                    break;
            }
        }

        if (!empty($conditions['teacherId'])) {
            $prepareConditions['courseIds'] = ArrayToolkit::column($this->getMemberService()->findMembersByUserIdAndRoles($conditions['teacherId'], ['teacher']), 'courseId');
        }

        if (!empty($conditions['type'])) {
            $prepareConditions['type'] = $conditions['type'];
        }

        return $prepareConditions;
    }

    private function prepareOrderBys($orderBys)
    {
        $prepareOrderBys = ['createdTime' => 'DESC'];

        if (!empty($orderBys['priceSort'])) {
            $prepareOrderBys = 'ASC' == $orderBys['priceSort'] ? ['price' => 'ASC'] : ['price' => 'DESC'];
        }

        if (!empty($orderBys['studentNumSort'])) {
            $prepareOrderBys = 'ASC' == $orderBys['studentNumSort'] ? ['studentNum' => 'ASC'] : ['studentNum' => 'DESC'];
        }

        if (!empty($orderBys['createdTimeSort'])) {
            $prepareOrderBys = 'DESC' == $orderBys['createdTimeSort'] ? ['createdTime' => 'DESC'] : ['createdTime' => 'ASC'];
        }

        return $prepareOrderBys;
    }

    private function makeMultiClassesInfo($multiClasses)
    {
        $multiClassIds = ArrayToolkit::column($multiClasses, 'id');
        $courseIds = ArrayToolkit::column($multiClasses, 'courseId');
        $productIds = ArrayToolkit::column($multiClasses, 'productId');

        $teachers = $this->getMemberService()->findMultiClassMembersByMultiClassIdsAndRole($multiClassIds, 'teacher');
        $assistants = $this->getMemberService()->findMultiClassMembersByMultiClassIdsAndRole($multiClassIds, 'assistant');
        $teacherUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($teachers, 'userId'));
        $assistantUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($assistants, 'userId'));
        $teachers = ArrayToolkit::index($teachers, 'multiClassId');
        $assistantGroup = ArrayToolkit::group($assistants, 'multiClassId');

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $products = $this->getMultiClassProductService()->findProductByIds($productIds);

        foreach ($multiClasses as &$multiClass) {
            $teacher = $teachers[$multiClass['id']];
            $assistants = empty($assistantGroup[$multiClass['id']]) ? [] : $assistantGroup[$multiClass['id']];
            $assistantIds = ArrayToolkit::column($assistants, 'userId');
            $multiClass['status'] = $this->getMultiClassStatus($multiClass['start_time'], $multiClass['end_time']);
            if ('group' == $multiClass['type']) {
                $multiClass['maxServiceNum'] = count($assistantIds) > 0 ? $multiClass['service_group_num'] * $multiClass['group_limit_num'] * count($assistantIds) : 0;
            } else {
                $multiClass['maxServiceNum'] = count($assistantIds) > 0 ? $multiClass['service_num'] * count($assistantIds) : 0;
            }

            $multiClass['course'] = empty($courses[$multiClass['courseId']]) ? [] : $courses[$multiClass['courseId']];
            $multiClass['product'] = $products[$multiClass['productId']]['title'];
            $multiClass['taskNum'] = $this->getTaskService()->countTasks(['courseId' => $multiClass['courseId'], 'status' => 'published', 'isLesson' => 1]);
            $multiClass['notStartLiveTaskNum'] = $this->getTaskService()->countTasks([
                'courseId' => $multiClass['courseId'],
                'status' => 'published',
                'isLesson' => 1,
                'type' => 'live',
                'startTime_GT' => time(),
            ]);
            $multiClass['endTaskNum'] = $this->getTaskService()->countTasks([
                'courseId' => $multiClass['courseId'],
                'status' => 'published',
                'isLesson' => 1,
                'endTime_LT' => time(),
            ]);
            $multiClass['teacherId'] = $teacherUsers[$teacher['userId']]['id'];
            $multiClass['teacher'] = $teacherUsers[$teacher['userId']]['nickname'];
            $multiClass['assistantIds'] = $assistantIds;
            $multiClass['assistant'] = [];
            array_walk($assistantIds, function ($id) use (&$multiClass,$assistantUsers) {
                $multiClass['assistant'][] = $assistantUsers[$id]['nickname'];
            });
        }

        return $multiClasses;
    }

    private function getMultiClassStatus($startTime, $endTime)
    {
        if ($startTime > time()) {
            return 'notStart';
        } elseif ($startTime <= time() && time() <= $endTime) {
            return 'living';
        } elseif ($endTime < time()) {
            return 'end';
        }
    }

    private function checkDataFields($multiClass)
    {
        if (!ArrayToolkit::requireds($multiClass, ['title', 'courseId', 'productId', 'type'])) {
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

        if (in_array($multiClass['teacherId'], $multiClass['assistantIds'])) {
            throw MultiClassException::MULTI_CLASS_TEACHER_CANNOT_BE_ASSISTANT();
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
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
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

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->service('Course:MemberService');
    }
}
