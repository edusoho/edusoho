<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;

class LatestCourseMembersDataTag extends CourseBaseDataTag implements DataTag  
{

    /**
     * 获取一个分类下的课程成员列表
     *
     * 可传入的参数：
     *   categoryId 必需 分类ID
     *   count    必需 课程数量，取值不能超过100
     * 
     * @param  array $arguments 参数
     * @return array 课程成员列表
     */
    public function getData(array $arguments)
    {	
        $this->checkCount($arguments);
        $conditions = array('status' => 'published', 'categoryId' => $arguments['categoryId']);
        $courses = $this->getCourseService()->searchCourses($conditions,'latest', 0, 1000);

        $courseIds = ArrayToolkit::column($courses, 'id');

        $conditions = array('courseIds' => $courseIds, 'unique' => true);
        $memberIds = $this->getCourseService()->searchMemberIds($conditions, array('createdTime', 'DESC'), 0, $arguments['count']);

        $users = $this->getUserService()->findUsersByIds($memberIds);

        return $this->unsetUserPasswords($users);
    }
}
