<?php

namespace AppBundle\Extensions\DataTag;

class MemberRecentlyLearnedDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取个人正在学习课程.
     *
     *   user     必须
     *
     * @param array $arguments 参数
     *
     * @return array 个人正在学习课程相关信息
     */
    public function getData(array $arguments)
    {
        $user = $arguments['user'];

        $task = $this->getTaskService()->getUserRecentlyStartTask($user->id);

        if (!empty($task)) {
            $courseSet = $this->getCourseSetService()->getCourseSet($task['fromCourseSetId']);
            $course = $this->getCourseService()->getCourse($task['courseId']);

            $member = $this->getCourseMemberService()->getCourseMember($course['id'], $user->id);
            $course['teachers'] = $this->getUserService()->findUsersByIds($course['teacherIds']);
            $course['nextLearnTask'] = $this->getTaskService()->getNextTask($task['id']);
            $course['progress'] = $this->calculateUserLearnProgress($course, $member);
            $courseSet['course'] = $course;
        } else {
            $courseSet = array();
        }

        return $courseSet;
    }

    private function calculateUserLearnProgress($course, $member)
    {
        if ($course['taskNum'] == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($member['learnedNum'] / $course['taskNum'] * 100).'%';

        return array(
            'percent' => $percent,
            'number' => $member['learnedNum'],
            'total' => $course['taskNum'],
        );
    }
}
