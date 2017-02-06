<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Biz\Task\Dao\TaskResultDao;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Topxia\Common\ArrayToolkit;

class CourseMissionsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取我的班级中未学的任务
     * 可传入的参数：
     *   userId         用户
     *   count          课程数量
     *   missionCount   任务数量
     *
     * @param  array $arguments 参数
     * @return array 按课程分组的任务列表
     */
    public function getData(array $arguments)
    {
        if (!ArrayToolkit::requireds($arguments, array('userId', 'count', 'missionCount'))) {
            throw new \InvalidArgumentException("参数缺失");
        }

        return $this->getCourseStudyMissions($arguments);
    }

    private function getCourseStudyMissions($arguments)
    {
        $userId = $arguments['userId'];

        $sortedCourses = array();

        $courseMemConditions = array(
            'userId'      => $userId,
            'locked'      => 0,
            'classroomId' => 0,
            'role'        => 'student'
        );

        $searchMembers = $this->getCourseMemberService()->searchMembers($courseMemConditions, array('createdTime' => 'DESC'), 0, 5);
        $courseIds     = ArrayToolkit::column($searchMembers, 'courseId');

        if (!empty($courseIds)) {
            $courseConditions = array(
                'courseIds' => $courseIds,
                'parentId'  => 0
            );
            $courses          = $this->getCourseService()->searchCourses($courseConditions, 'default', 0, $arguments['count']);
            $courses          = ArrayToolkit::index($courses, 'id');

            foreach ($searchMembers as $member) {
                if (empty($courses[$member['courseId']])) {
                    continue;
                }

                $course          = $courses[$member['courseId']];
                $sortedCourses[] = $course;
            }

            foreach ($sortedCourses as $key => &$course) {

                $conditions = array(
                    'userId'   => $userId,
                    'courseId' => $course['id'],
                    'status'   => 'finish'
                );

                $finishTaskCount = $this->getTaskResultService()->countTaskResults($conditions);

                $toLearnTasks = $this->getTaskService()->findToLearnTasksByCourseIdForMisson($course['id'], false);
                $course['tasks']         = $toLearnTasks;
                $course['finishTaskNum'] = $finishTaskCount;

            }
        }

        return $sortedCourses;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->getServiceKernel()->createService('Task:TaskResultService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }
}
