<?php

namespace AppBundle\Component\Wrapper;

use Topxia\Service\Common\ServiceKernel;
use Codeages\PluginBundle\System\PluginConfigurationManager;

class CouponBatchWrapper extends Wrapper
{
    public function targetContent($batch)
    {
        $targetType = $batch['targetType'];
        $targetId = $batch['targetId'];
        $couponContents = array(
            'all' => '全站可用',
            'vip' => '全部会员',
            'course' => '全部课程',
            'classroom' => '全部班级',
        );

        $couponContent = 'multi';
        $targetCount = $this->getCouponBatchResourceService()->countCouponBatchResource(array('batchId' => $batch['id']));

        if (!empty($targetId) && 'vip' != $targetType && $targetCount > 0) {
            if (1 == $targetCount) {
                $target = $this->getCouponBatchResourceService()->searchCouponBatchResource(array('batchId' => $batch['id']), array('id' => 'ASC'), 0, 1);
                $target = array_shift($target);
            } else {
                $batch['targetContent'] = $couponContent;

                return $batch;
            }
        }

        $targetId = empty($target['targetId']) ? $targetId : $target['targetId'];
        if (0 == $targetId || 'all' == $targetType) {
            $couponContent = $couponContents[$targetType];
        } elseif ('course' == $targetType) {
            $course = $this->getCourseSetService()->getCourseSet($targetId);
            $couponContent = '课程:'.$course['title'];
        } elseif ('classroom' == $targetType) {
            $classroom = $this->getClassroomService()->getClassroom($targetId);
            $couponContent = '班级:'.$classroom['title'];
        } elseif ('vip' == $targetType && $this->isPluginInstalled('Vip')) {
            $level = $this->getLevelService()->getLevel($targetId);
            $couponContent = '会员:'.$level['name'];
        }
        $batch['targetContent'] = $couponContent;

        return $batch;
    }

    public function targetType()
    {
        return
    }

    protected function getWrapList()
    {
        return array(
            'targetContent'
        );
    }

    protected function getCourseSetService()
    {
        return ServiceKernel::instance()->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return CouponBatchResourceService
     */
    private function getCouponBatchResourceService()
    {
        return ServiceKernel::instance()->getBiz()->service('Coupon:CouponBatchResourceService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return ServiceKernel::instance()->getBiz()->service('Classroom:ClassroomService');
    }

    private function getLevelService()
    {
        return ServiceKernel::instance()->getBiz()->service('VipPlugin:Vip:LevelService');
    }

    protected function isPluginInstalled($code)
    {
        $pluginManager = new PluginConfigurationManager(ServiceKernel::instance()->getParameter('kernel.root_dir'));

        return $pluginManager->isPluginInstalled($code);
    }
}
