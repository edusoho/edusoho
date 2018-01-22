<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;

class LatestCourseMembers2DataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取一个分类下的课程成员列表.
     *
     * 可传入的参数：
     *   categoryId 选填 分类ID
     *   count    必需 学员数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 课程成员列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);
        if (empty($arguments['categoryId'])) {
            $conditions = array('status' => 'published');
        } else {
            $conditions = array('status' => 'published', 'categoryId' => $arguments['categoryId']);
        }
        $courses = $this->getCourseService()->searchCourses($conditions, 'latest', 0, 1000);
        if (empty($courses)) {
            return array();
        }

        $courseIds = ArrayToolkit::column($courses, 'id');
        $conditions = array('courseIds' => $courseIds, 'unique' => true, 'role' => 'student');

        $members = $this->getCourseMemberService()->searchMembers($conditions, array('createdTime' => 'DESC'), 0, $arguments['count']);
        $courseIds = ArrayToolkit::column($members, 'courseId');
        $userIds = ArrayToolkit::column($members, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');
        $this->unsetUserPasswords($users);
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');

        foreach ($members as &$member) {
            $member['course'] = $courses[$member['courseId']];
            $member['user'] = $users[$member['userId']];
        }

        return $members;
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->getBiz()->service('Course:MemberService');
    }
}
