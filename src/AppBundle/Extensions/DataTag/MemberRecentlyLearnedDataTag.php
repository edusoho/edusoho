<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Course\Service\LearningDataAnalysisService;

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

        $task = $this->getTaskService()->getUserRecentlyStartTask($user['id']);

        if (!empty($task)) {
            $courseSet = $this->getCourseSetService()->getCourseSet($task['fromCourseSetId']);
            $course = $this->getCourseService()->getCourse($task['courseId']);

            $member = $this->getCourseMemberService()->getCourseMember($course['id'], $user['id']);
            if (empty($member)) {
                return  array();
            }

            $course['teachers'] = $this->getUserService()->findUsersByIds($course['teacherIds']);
            $course['nextLearnTask'] = $this->getTaskService()->getNextTask($task['id']);
            $course['progress'] = $this->getLearningDataAnalysisService()->getUserLearningProgress($course['id'], $member['userId']);
            $courseSet['course'] = $course;
        } else {
            $courseSet = array();
        }

        return $courseSet;
    }

    /**
     * @return LearningDataAnalysisService
     */
    private function getLearningDataAnalysisService()
    {
        return $this->getServiceKernel()->createService('Course:LearningDataAnalysisService');
    }
}
