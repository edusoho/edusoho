<?php

namespace ApiBundle\Api\Resource\StudentLiveCourse;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use ApiBundle\Api\Exception\ErrorCode;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StudentLiveCourse extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        if (empty($conditions['createdTime_GE']) || empty($conditions['createdTime_LT'])) {
            throw new BadRequestHttpException('Params missing', null, ErrorCode::INVALID_ARGUMENT);
        }
        $user = $this->getCurrentUser();
        $liveCourses = $this->findLiveCourse($conditions, $user['id']);
        return array('data' => $liveCourses);
    }

    protected function findLiveCourse($conditions, $userId)
    {
        $members = $this->getMemberService()->searchMembers(
            array('userId' => $userId, 'role' => 'student'), array(), 0, PHP_INT_MAX
        );
        $courseIds = ArrayToolkit::column($members, 'courseId');
        if (empty($courseIds)) {
            return array();
        } else {
            $liveCourses = array();
            $tasks = $this->getTaskService()->searchTasks(
                array('courseIds' => $courseIds, 'type' => 'live', 'startTime_GE' => $conditions['createdTime_GE'], 'endTime_LT' => $conditions['createdTime_LT'], 'status' => 'published'),
                array(),
                0,
                PHP_INT_MAX
            );
            foreach ($tasks as $task) {
                $course = $this->getCourseSetService()->searchCourseSets(
                    array('id' => $task['courseId'], 'status' => 'published'), array(), 0, PHP_INT_MAX
                );
                if (!empty($course)) {
                    $liveCourse = array();
                    $liveCourse['title'] = $course[0]['title'];
                    $liveCourse['startTime'] = date("Y-m-d H:i:s", $task['startTime']);
                    $liveCourse['endTime'] = date("Y-m-d H:i:s", $task['endTime']);
                    array_push($liveCourses, $liveCourse);
                }
            }
            return $liveCourses;
        }
    }

    /**
     * @return MemberService
     */
    private function getMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
       return $this->service('Course:CourseSetService');
    }
}