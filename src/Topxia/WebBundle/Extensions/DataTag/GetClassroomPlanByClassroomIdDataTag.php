<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

/**
 * @todo  
 */
class GetClassroomPlanByClassroomIdDataTag extends BaseDataTag implements DataTag  
{

    /**
     * 获取用户今日任务
     *
     * 可传入的参数：
     *   classroomId   必需 班级ID
     * 
     * @param  array $arguments 参数
     * @return array 学习计划
     */
    public function getData(array $arguments)
    {   
        $classroomPlan = $this->getClassroomPlanService()->getPlanByClassroomId($arguments['classroomId']);

        return $classroomPlan;
    }

    protected function getClassroomPlanService()
    {
        return $this->getServiceKernel()->createService('ClassroomPlan:ClassroomPlan.ClassroomPlanService');
    }
}
