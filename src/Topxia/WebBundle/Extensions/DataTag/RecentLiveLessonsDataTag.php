<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;

class RecentLiveLessonsDataTag extends CourseBaseDataTag implements DataTag
{

    /**
     * 获取用户近期直播课时列表
     * 可传入的参数：
     *   userId    可选 用户ID
     *   count     必需 课时数量，取值不能超过100
     * 
     * @param  array $arguments 参数
     * @return array 课时列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);

        $filters['type'] = 'live';

        if(isset($arguments['userId'])) {
            $userId = $arguments['userId'];
            $memConditions = array(
                'userId' => $userId
            );
            $userCourseCount = $this->getCourseService()->searchMemberCount($memConditions);
            $liveCourses = $this->getCourseService()->findUserLeaningCourses($userId,0,$userCourseCount,$filters);

            $courseIds = ArrayToolkit::column($liveCourses,'id');
            if(empty($courseIds)){
                return array();
            }
        }
        $conditions = array(
            'status' => 'published',
            'type' => 'live',
            'endTimeGreaterThan' => time()
        );

        if(isset($courseIds)) {
            $conditions["courseIds"] = $courseIds;
        }

        $sort = array(
            'startTime','ASC'
        );

        return $this->getCourseService()->searchLessons($conditions,$sort,0, $arguments['count']);

    }

}
