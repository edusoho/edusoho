<?php
namespace Custom\Service\Course\Impl;

use Custom\Service\Course\CourseOrder1Service;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\Impl\CourseOrderServiceImpl;

class CourseOrder1ServiceImpl extends CourseOrderServiceImpl implements CourseOrder1Service
{   
    protected function getOrderService()
    {
        return $this->createService('Custom:Order.Order1Service');
    }

    public function doSuccessPayOrder($id)
    {   
        $order = $this->getOrderService()->getOrder($id);
        if (empty($order) or $order['targetType'] != 'course') {
            throw $this->createServiceException('非课程订单，加入课程失败。');
        }

        $info = array(
            'orderId' => $order['id'],
            'remark'  => empty($order['data']['note']) ? '' : $order['data']['note'],
        );

        if (!$this->getCourseService()->isCourseStudent($order['targetId'], $order['userId'])) {
            $this->getCourseService()->becomeStudent($order['targetId'], $order['userId'], $info);
    
            $log=array(
                    'title'=>$order['title'],
                    'userId'=>$order['userId'],
                    'type'=>'in',
                    'createdTime'=>time(),
                    'point'=>$order['amount']*0.7,
                    'amount'=>$order['amount'],
                    );

            $this->getVipService()->addPointLog($log);
        }

        return ;
    }

    protected function getVipService()
    {
        return $this->createService('Vip:Vip.VipService');
    } 
}