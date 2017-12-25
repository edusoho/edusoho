<?php

namespace ApiBundle\Api\Resource\TeacherLiveCourse;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use ApiBundle\Api\Exception\ErrorCode;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TeacherLiveCourse extends AbstractResource
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
        $openLiveCourses = $this->findOpenLiveCourse($conditions, $user['id']);
        return array('data' => array_merge($liveCourses, $openLiveCourses));
    }

    protected function findLiveCourse($conditions, $userId)
    {
        $members = $this->getMemberService()->searchMembers(
            array('userId' => $userId, 'role' => 'teacher'), array(), 0, PHP_INT_MAX
        );
        $courseIds = ArrayToolkit::column($members, 'courseId');
        $liveCourses = array();
        if (!empty($courseIds)) {
            $tasks = $this->getTaskService()->searchTasks(
                array('courseIds' => $courseIds, 'type' => 'live', 'startTime_GE' => $conditions['createdTime_GE'], 'endTime_LT' => $conditions['createdTime_LT'], 'status' => 'published'),
                array(),
                0,
                PHP_INT_MAX
            );
            foreach ($tasks as $task) {
                $course = $this->getCourseService()->searchCourses(
                    array('id' => $task['courseId'], 'status' => 'published'), array(), 0, PHP_INT_MAX
                );
                if (!empty($course)) {
                    $courseSet = $this->getCourseSetService()->searchCourseSets(
                        array('id' => $course[0]['courseSetId'], 'status' => 'published'), array(), 0, PHP_INT_MAX
                    );
                    if (!empty($courseSet)) {
                        $liveCourse = array();
                        $liveCourse['title'] = $courseSet[0]['title'];
                        $liveCourse['url'] = '/course/' . $task['courseId'] . '/task/' . $task['id'] . '/show';
                        $liveCourse['event'] = $courseSet[0]['title'] . '-' . $course[0]['title'] . '-' . $task['title'];
                        $liveCourse['startTime'] = date("Y-m-d H:i:s", $task['startTime']);
                        $liveCourse['endTime'] = date("Y-m-d H:i:s", $task['endTime']);
                        array_push($liveCourses, $liveCourse);
                    }
                }
            }
        }
        return $liveCourses;
    }

    protected function findOpenLiveCourse($conditions, $userId)
    {
        $members = $this->getOpenCourseService()->searchMembers(
            array('userId' => $userId, 'role' => 'teacher'), array(), 0, PHP_INT_MAX
        );
        $courseIds = ArrayToolkit::column($members, 'courseId');
        $openLiveCourses = array();
        if (!empty($courseIds)) {
            $openLessons = $this->getOpenCourseService()->searchLessons(
                array('courseIds' => $courseIds, 'type' => 'liveOpen', 'startTimeGreaterThan' => $conditions['createdTime_GE'], 'endTimeLessThan' => $conditions['createdTime_LT'], 'status' => 'published'),
                array(),
                0,
                PHP_INT_MAX
            );
            foreach ($openLessons as $openLesson) {
                $openCourse = $this->getOpenCourseService()->getCourse($openLesson['courseId']);
                if (!empty($openCourse)) {
                    $openLiveCourse = array();
                    $openLiveCourse['title'] = $openCourse['title'];
                    $openLiveCourse['event'] = $openCourse['title'];
                    $openLiveCourse['url'] = '/open/course/' . $openCourse['id'];
                    $openLiveCourse['startTime'] = date("Y-m-d H:i:s", $openLesson['startTime']);
                    $openLiveCourse['endTime'] = date("Y-m-d H:i:s", $openLesson['endTime']);
                    array_push($openLiveCourses, $openLiveCourse);
                }
            }
        }
        return $openLiveCourses;
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
     * @return OpenCourseService
     */
    private function getOpenCourseService()
    {
        return $this->service('OpenCourse:OpenCourseService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
       return $this->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}