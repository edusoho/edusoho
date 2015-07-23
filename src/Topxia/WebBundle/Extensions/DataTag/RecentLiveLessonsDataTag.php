<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;

class RecentLiveLessonsDataTag extends CourseBaseDataTag implements DataTag
{

    /**
     * 获取用户近期直播课时列表
     * 可传入的参数：
     *   userId    必需 用户ID
     * 
     * @param  array $arguments 参数
     * @return array 课时列表
     */
    public function getData(array $arguments)
    {
        $this->checkUserId($arguments);
        $userId = $arguments['userId'];
        $filters['type'] = 'live';
        $memConditions = array(
            'userId' => $userId
        );
        $userCourseCount = $this->getCourseService()->searchMemberCount($memConditions);
        $liveCourses = $this->getCourseService()->findUserLeaningCourses($userId,0,$userCourseCount,$filters);
        $recentLiveLessons = array();
        if(!empty($liveCourses)){
            $conditions = array(
                'status' => 'published',
                'courseIds' => ArrayToolkit::column($liveCourses,'id'),
                'type' => 'live',
                'endTimeGreaterThan' => time()
            );
            $sort = array(
                'startTime','ASC'
            );
            $recentLiveLessons = $this->getCourseService()->searchLessons($conditions,$sort,0,2);
        }

        return $recentLiveLessons;

    }

}
