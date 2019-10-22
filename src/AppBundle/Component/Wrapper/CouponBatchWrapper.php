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
            'all' => $this->getServiceKernel()->trans('coupon.for_any_purchase_on_our_web_site'),
            'vip' => $this->getServiceKernel()->trans('coupon.target_type.vip_all'),
            'course' => $this->getServiceKernel()->trans('coupon.target_type.course_all'),
            'classroom' => $this->getServiceKernel()->trans('coupon.target_type.classroom_all'),
        );

        $couponContent = 'multi';

        if (!empty($targetId) && 'vip' != $targetType && count($batch['targetIds']) > 0) {
            if (count($batch['targetIds']) > 1) {
                $batch['targetContent'] = $couponContent;

                return $batch;
            } else {
                $targetId = $batch['targetIds'][0];
            }
        }

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

    public function targetDetail($batch)
    {
        list($productType, $numType) = $this->getProductTypeAndNumType($batch);
        $data = array();
        if ('all' != $numType) {
            switch ($productType) {
                case 'course':
                    $data = $this->getCourseSetService()->findCourseSetsByIds($batch['targetIds']);
                    break;
                case 'classroom':
                    $data = $this->getClassroomService()->findClassroomsByIds($batch['targetIds']);
                    break;
                case 'vip':
                    if ($this->isPluginInstalled('Vip')) {
                        $vip = $this->getLevelService()->getLevel($batch['targetId']);
                        $data = array($vip);
                    }
                    // no break
                default:
                    break;
            }
            $data = array_values($data);
        }
        $batch['targetDetail'] = array(
            'product' => $productType,
            'numType' => $numType,
            'data' => $data,
        );

        return $batch;
    }

    protected function getProductTypeAndNumType($batch)
    {
        $productType = $batch['targetType'];
        $numType = 'single';
        if (0 == $batch['targetId']) {
            $numType = 'all';
        }
        if (!empty($batch['targetId']) && count($batch['targetIds']) > 1) {
            $numType = 'multi';
        }

        return array($productType, $numType);
    }

    protected function getWrapList()
    {
        return array(
            'targetContent',
            'targetDetail',
        );
    }

    private function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function getCourseSetService()
    {
        return $this->getServiceKernel()->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->getServiceKernel()->getBiz()->service('Classroom:ClassroomService');
    }

    private function getLevelService()
    {
        return $this->getServiceKernel()->getBiz()->service('VipPlugin:Vip:LevelService');
    }

    protected function isPluginInstalled($code)
    {
        $pluginManager = new PluginConfigurationManager($this->getServiceKernel()->getParameter('kernel.root_dir'));

        return $pluginManager->isPluginInstalled($code);
    }
}
