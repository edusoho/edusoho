<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;

class RecentLiveTasksDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取用户近期直播课时列表
     * 可传入的参数：
     *   userId    可选 用户ID
     *   count     必需 课时数量，取值不能超过100.
     *
     * @param array $arguments 参数
     *
     * @return array 课时列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);

        if (isset($arguments['userId'])) {
            $userId = $arguments['userId'];
            //普通课程也包含直播任务
            $courseMembers = $this->getCourseMemberService()->findStudentMemberByUserId($userId);
            $courseIds = ArrayToolkit::column($courseMembers, 'courseId');
            if (empty($courseIds)) {
                return array();
            }
        }
        $conditions = array(
            'status' => 'published',
            'type' => 'live',
            'endTime_GT' => time(),
        );

        if (isset($courseIds)) {
            $conditions['courseIds'] = $courseIds;
        }

        $sort = array(
            'startTime' => 'ASC',
        );

        return $this->getTaskService()->searchTasks($conditions, $sort, 0, $arguments['count']);
    }
}
