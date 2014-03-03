<?php
namespace Topxia\Service\Delivery\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Delivery\CommissionService;
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


    public function getCommissionsByOrder($order)
    {
        return CommissionSerialize::unserializes($this->getCommissionDao()->getCommissionsByOrder($order));
    }



    public function searchCommissions($conditions, $sort = 'latest', $start, $limit)
    {
        $conditions = $this->_prepareCommissionConditions($conditions);
        
        $orderBy = array('createdTime', 'DESC');
        
        
        return CommissionSerialize::unserializes($this->getCommissionDao()->searchCommissions($conditions, $orderBy, $start, $limit));
    }


    public function searchCommissionCount($conditions)
    {
        $conditions = $this->_prepareCommissionConditions($conditions);
        return $this->getCommissionDao()->searchCommissionCount($conditions);
    }

    public function computeMyCommissionsOfYesterday($partnerId){

        return  $this->getCommissionDao()->computeMyCommissionsOfYesterday($partnerId);
    }

    public function computeMyCommissionsOfMonth($partnerId){

        return  $this->getCommissionDao()->computeMyCommissionsOfMonth($partnerId);
    }

    public function computeMyCommissionsOfLast($partnerId){

        return  $this->getCommissionDao()->computeMyCommissionsOfLast($partnerId);
    }

    public function computeMyCommissions($partnerId){

        return  $this->getCommissionDao()->computeMyCommissions($partnerId);
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

        $commission = ArrayToolkit::parts($commission, array('id','buyerIP', 'saleType','saleId','saleTookeen','buyerId','salerId', 'orderId', 'orderSn', 'orderPrice','commission','note', 'status', 'drawedTime','paidTime','updatedTime','createdTime'));

        $commission['createdTime']=time();

        $commission['buyerIP'] = $this->getCurrentUser()->currentIp;

        $commission = $this->getCommissionDao()->addCommission(CommissionSerialize::serialize($commission));

        $this->getLogService()->info('commission', 'add_commission', "记录渠道《{$commission['salerId']}》在订单《{$commission['orderSn']}》的佣金", $commission);

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


    public function computeLinkSaleCommission($order,$linksale)
    {

         if($linksale['prodType']=='course'){

            $commission['saleType']= $linksale['saleType'];
            $commission['saleId']= $linksale['id'];
            $commission['saleTookeen'] = $linksale['mTookeen'];
            $commission['buyerId'] = $order['userId'];
            $commission['salerId'] = $linksale['partnerId'];
            $commission['orderId'] = $order['id'];
            $commission['orderSn'] = $order['sn'];
            $commission['orderPrice'] = $order['price'];


            if($order['userId']==$linksale['partnerId']){

                 $commission['commission']=0;
                 $commission['note']='本人定单不能享受佣金收入';


            }else if (!empty($linksale['validTimeNum']) and $linksale['validTimeNum']<time()){

                 $commission['commission']=0;
                 $commission['note']='已过推广有效期，本笔定单不能享受佣金收入';

            }else if($linksale['parnterIP'] ==  $this->getCurrentUser()->currentIp){

                 $commission['commission']=0;
                 $commission['note']='购买人IP与推广人IP相同，不能享受佣金收入';

            }else if(!empty($order['promoCode'])){

                 $commission['commission']=0;
                 $commission['note']='该订单已被优惠码推广，不能享受佣金收入';

            }else{

                if($linksale['adCommissionType']=='ratio'){

                    $commission['commission']= ($order['price']*$linksale['adCommission'])/100;

                }else if ($linksale['adCommissionType']=='quota'){

                     $commission['commission']= $linksale['adCommission'];

                }else
                {
                    $commission['commission']=0;
                }
            }

            $commission['status']='created';

            return $this->createCommission($commission);
           
        }
       
    }

    public function computeOffSaleCommission($order,$offsale)
    {

         if($offsale['prodType']=='course' or $offsale['prodType']=='课程'){

            $commission['saleType']= empty($offsale['saleType'])?'offsale-course':$offsale['saleType'];
            $commission['saleId']= $offsale['id'];
            $commission['saleTookeen'] = $offsale['promoCode'];
            $commission['buyerId'] = $order['userId'];
            $commission['salerId'] = $offsale['partnerId'];
            $commission['orderId'] = $order['id'];
            $commission['orderSn'] = $order['sn'];
            $commission['orderPrice'] = $order['price'];

            if($order['userId']==$offsale['partnerId']){

                 $commission['commission']=0;
                 $commission['note']='本人定单不能享受佣金收入';


            }else if (!empty($offsale['validTimeNum']) and $offsale['validTimeNum']<time()){

                 $commission['commission']=0;
                 $commission['note']='已过推广有效期，本笔定单不能享受佣金收入';

            }else if($offsale['parnterIP'] ==  $this->getCurrentUser()->currentIp){

                 $commission['commission']=0;
                 $commission['note']='购买人IP与推广人IP相同，不能享受佣金收入';

            }else{

                if($offsale['adCommissionType']=='ratio'){

                    $commission['commission']= ($order['price']*$offsale['adCommission'])/100;

                }else if ($offsale['adCommissionType']=='quota'){

                     $commission['commission']= $offsale['adCommission'];

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

        $commissions = $this->getCommissionsByOrder($order);

        foreach ($commissions as $commission ) {

            if(!empty($commission)){

                $this->updateCommission($commission['id'], array(
                        'status' => 'paid',
                        'paidTime' => $order['paidTime'],
                    ));

                $this->getLogService()->info('commission', 'comfirm_commission', "确认渠道《{$commission['salerId']}》在订单《{$commission['orderSn']}》的佣金", $commission);  
            }
            
        }
         
    }

    public function frozenCommissionWithOrder($order)
    {

        

    }

    public function unFrozenCommissionWithOrder($order)
    {

      

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