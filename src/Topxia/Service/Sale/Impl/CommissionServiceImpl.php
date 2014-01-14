<?php
namespace Topxia\Service\Sale\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Sale\CommissionService;
use Topxia\Common\ArrayToolkit;

class CommissionServiceImpl extends BaseService implements CommissionService
{

    public function findCommissionsByIds(array $ids)
    {
        $commissions =  CommissionSerialize::unserializes(
             $this->getCommissionDao()->findCommissionsByIds($ids)
        );

        return ArrayToolkit::index($commissions, 'id');
    }

    public function getCommission($id)
    {
        return CommissionSerialize::unserialize($this->getCommissionDao()->getCommission($id));
    }


    public function getCommissionByOrder($order)
    {
        return MySaleSerialize::unserialize($this->getCommissionDao()->getCommissionByOrder($order));
    }



    public function searchCommissions($conditions, $sort = 'latest', $start, $limit)
    {
        $conditions = $this->_prepareCommissionConditions($conditions);
        if ($sort == 'popular') {
            $orderBy =  array('hitNum', 'DESC');
        } else if ($sort == 'recommended') {
            $orderBy = array('recommendedTime', 'DESC');
        } else {
            $orderBy = array('createdTime', 'DESC');
        }
        
        return CommissionSerialize::unserializes($this->getCommissionDao()->searchCommissions($conditions, $orderBy, $start, $limit));
    }


    public function searchCommissionCount($conditions)
    {
        $conditions = $this->_prepareCommissionConditions($conditions);
        return $this->getCommissionDao()->searchCommissionCount($conditions);
    }

    private function _prepareCommissionConditions($conditions)
    {
        $conditions = array_filter($conditions);
        if (isset($conditions['date'])) {
            $dates = array(
                'yesterday'=>array(
                    strtotime('yesterday'),
                    strtotime('today'),
                ),
                'today'=>array(
                    strtotime('today'),
                    strtotime('tomorrow'),
                ),
                'this_week' => array(
                    strtotime('Monday this week'),
                    strtotime('Monday next week'),
                ),
                'last_week' => array(
                    strtotime('Monday last week'),
                    strtotime('Monday this week'),
                ),
                'next_week' => array(
                    strtotime('Monday next week'),
                    strtotime('Monday next week', strtotime('Monday next week')),
                ),
                'this_month' => array(
                    strtotime('first day of this month midnight'), 
                    strtotime('first day of next month midnight'),
                ),
                'last_month' => array(
                    strtotime('first day of last month midnight'),
                    strtotime('first day of this month midnight'),
                ),
                'next_month' => array(
                    strtotime('first day of next month midnight'),
                    strtotime('first day of next month midnight', strtotime('first day of next month midnight')),
                ),
            );

            if (array_key_exists($conditions['date'], $dates)) {
                $conditions['startTimeGreaterThan'] = $dates[$conditions['date']][0];
                $conditions['startTimeLessThan'] = $dates[$conditions['date']][1];
                unset($conditions['date']);
            }
        }

        return $conditions;
    }


    public function createCommission($commission){

        $commission = ArrayToolkit::parts($commission, array('id', 'mysaleId','mTookeen','buyerId','userId', 'orderId', 'orderSn', 'commission', 'status', 'drawedTime','paidTime','updatedTime','createdTime'));

        $commission['createdTime']=time();

        $commission = $this->getCommissionDao()->addCommission(CommissionSerialize::serialize($commission));

        return $this->getCommission($commission['id']);

    }

    public function updateCommission($id, $commission)
    {

       return $this->getCommissionDao()->updateCommission($id, $commission);
    }


    public function deleteCommissions(array $ids)
    {
        foreach ($ids as $id) {
            $this->getCommissionDao()->deleteCommission($id);
        }
    }


    public function computeCommission($order,$mysale)
    {

         if($mysale['prodType']=='course'){

            $commission['mysaleId']= $mysale['id'];
            $commission['mTookeen'] = $mysale['mTookeen'];
            $commission['buyerId'] = $order['userId'];
            $commission['salerId'] = $mysale['userId'];
            $commission['orderId'] = $order['id'];
            $commission['orderSn'] = $order['sn'];
            $commission['orderPrice'] = $order['price'];

            if($order['userId']==$mysale['userId']){

                 $commission['commission']=0;
                 $commission['note']='本人购买的课程不能享受佣金收入';


            }else{

                if($mysale['adCommissionType']=='ratio'){

                    $commission['commission']= $mysale['adCommission']*$order['price']/100;   //30%

                }else if ($mysale['adCommissionType']=='quota'){

                     $commission['commission']= $mysale['adCommission'];

                }else
                {
                    $commission['commission']=0;
                }
            }

            $commission['status']='created';

            return $this->createCommission($commission);
           
        }  
       
    }


    public function confirmCommission($order)
    {

        $commission = $this->getCommissionByOrder($order);

         $this->updateCommission($commission['id'], array(
                    'status' => 'paid',
                    'paidTime' => $order['paidTime'],
                ));
    }

    public function frozenCommissionWithOrder($order)
    {

        $commission = $this->getCommissionByOrder($order);

        $this->frozenCommission($commission);

    }

    public function unFrozenCommissionWithOrder($order)
    {

        $commission = $this->getCommissionByOrder($order);

        $this->frozenCommission($commission);

    }

    public function applyDrawCommission($commission)
    {
        
    }

    public function agreeDrawCommission($commission)
    {
        
    }

    public function refuseDrawCommission($commission)
    {
        
    }

    public function cancelledApplyCommission($commission)
    {
        
    }

    public function drawCommission($commission)
    {
        
    }

    public function frozenCommission($commission)
    {

        return  $this->updateCommission($commission['id'], array(
                    'status' => 'frozen',
                    'updatedTime' => time(),
                ));
        
    }

    public function unfrozenCommission($commission)
    {

        return  $this->updateCommission($commission['id'], array(
                    'status' => 'paid',
                    'updatedTime' => time(),
                ));
        
    }
  

    private function getCommissionDao()
    {
        return $this->createDao('Sale.CommissionDao');
    }

    private function getActivityService()
    {
        return $this->createService('Activity.ActivityService');
    }


    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    private function getOrderService()
    {
        return $this->createService('Course.OrderService');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }

    private function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

}


class CommissionSerialize
{

     //将php对象变成数据库字段。。。数组变为以|连接的字符串,时间字符串变成时间戳数字。。。。
    public static function serialize(array &$commission)
    {
       
        if (isset($commission['strpaidTime'])) {
            if (!empty($commission['strpaidTime'])) {
                $commission['paidTime'] = strtotime($commission['strpaidTime']);
            }
        }
        unset($commission['strpaidTime']);


        return $commission;
    }

    //将数据库字段变成php对象。。。以|连接的字符串变为数组,时间戳数字变成时间字符串。。。。

    public static function unserialize(array $commission = null)
    {
        if (empty($commission)) {
            return $commission;
        }


        if(empty($commission['paidTime'])){
            $commission['paidTime']='';
        }else{
            $commission['paidTimeNum']=$commission['paidTime'];
            $commission['paidTime']=date("Y-m-d H:i",$commission['paidTime']);
        }

        return $commission;
    }

    public static function unserializes(array $commissions)
    {
        return array_map(function($commission) {
            return CommissionSerialize::unserialize($commission);
        }, $commissions);
    }
}