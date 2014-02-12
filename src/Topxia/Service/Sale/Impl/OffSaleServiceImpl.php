<?php
namespace Topxia\Service\Sale\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Sale\OffSaleService;
use Topxia\Common\ArrayToolkit;

class OffSaleServiceImpl extends BaseService implements OffSaleService
{

    public function findOffSalesByIds(array $ids)
    {
        $offsales =  OffSaleSerialize::unserializes(
             $this->getOffSaleDao()->findOffSalesByIds($ids)
        );

        return ArrayToolkit::index($offsales, 'id');
    }

    public function getOffSale($id)
    {
        return OffSaleSerialize::unserialize($this->getOffSaleDao()->getOffSale($id));
    }


    public function getOffSaleByCode($code)
    {
        return OffSaleSerialize::unserialize($this->getOffSaleDao()->getOffSaleByCode($code));
    }

    public function getOffSaleBySPPP($saleType,$partnerId,$prodType,$prodId)
    {
        return OffSaleSerialize::unserialize($this->getOffSaleDao()->getOffSaleBySPPP($saleType,$partnerId,$prodType,$prodId));
    }



    public function searchOffSales($conditions, $sort = 'latest', $start, $limit)
    {
        $conditions = $this->_prepareOffSaleConditions($conditions);
        if ($sort == 'popular') {
            $orderBy =  array('hitNum', 'DESC');
        } else if ($sort == 'recommended') {
            $orderBy = array('recommendedTime', 'DESC');
        } else {
            $orderBy = array('createdTime', 'DESC');
        }
        
        return OffSaleSerialize::unserializes($this->getOffSaleDao()->searchOffSales($conditions, $orderBy, $start, $limit));
    }


    public function searchOffSaleCount($conditions)
    {
        $conditions = $this->_prepareOffSaleConditions($conditions);
        return $this->getOffSaleDao()->searchOffSaleCount($conditions);
    }

    private function _prepareOffSaleConditions($conditions)
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


    public function createOffSale($offsale){

        $offsale = ArrayToolkit::parts($offsale, array( 'id','partnerIP','saleType','prodType','prodId', 'prodName','promoName', 'promoCode','adCommissionType','adCommission','adCommissionDay','customized','reduceType','reducePrice',  'strvalidTime','validTime', 'reuse', 'valid','partnerId','createdTime','managerId'));

        $offsale['createdTime']=time();

        $offsale['parnterIP'] = $this->getCurrentUser()->currentIp;

        $offsale = $this->getOffSaleDao()->addOffSale(OffSaleSerialize::serialize($offsale));

        return $this->getOffSale($offsale['id']);

    }

    public function createOffSales($offsetting){

        if(empty($offsetting)){
            return 0;
        }

        if($offsetting['prodType']=='course')
        {
            $course = $this->getCourseService()->getCourse($offsetting['prodId']);

            $offsetting['prodName'] = $course['title'];
            $offsetting['saleType']='offsale-course';
        }

        if($offsetting['prodType']=='activity')
        {
            $activity = $this->getActivityService()->getActivity($offsetting['prodId']);
            $offsetting['prodName'] = $activity['title'];
            $offsetting['saleType']='offsale-activity';
        }

        for ($i = 1; $i<= $offsetting['promoNum']; $i++) {

            $offsale['saleType'] = $offsetting['saleType'];
            $offsale['prodType'] = $offsetting['prodType'];
            $offsale['prodName'] = $offsetting['prodName'];
            $offsale['prodId']  = $offsetting['prodId'];
            $offsale['promoName'] = $offsetting['promoName'];
            $offsale['promoCode']= $this->generateOffSaleCode($offsetting['promoPrefix']);
            $offsale['adCommissionType'] = $offsetting['adCommissionType'];
            $offsale['adCommission'] = $offsetting['adCommission'];
            $offsale['reduceType'] = $offsetting['reduceType'];
            $offsale['reducePrice'] = $offsetting['reducePrice'];
            $offsale['reuse']= $offsetting['reuse'];
            $offsale['valid']= $offsetting['valid'];
            $offsale['strvalidTime']= $offsetting['strvalidTime'];
            $offsale['partnerId']= $offsetting['partnerId'];
            $offsale['managerId']= $offsetting['managerId'];
           
            $this->createOffSale($offsale);        
        }

        
    }

    public function updateCourseOffSale4unCustomized($adCommissionType,$adCommission,$adCommissionDay,$courseId)
    {

        return $this->getOffSaleDao()->updateCourseOffSale4unCustomized($adCommissionType,$adCommission,$adCommissionDay,$courseId);
    }


    public function deleteOffSales(array $ids)
    {
        foreach ($ids as $id) {
            $this->getOffSaleDao()->deleteOffSale($id);
        }
    }

    public function checkProd($offsetting){

        if(empty($offsetting)){
            return array('hasProd'=>'false',"prodName"=>"此商品不存在");
        }

        if($offsetting['prodType']=='course')
        {
            $course = $this->getCourseService()->getCourse($offsetting['prodId']);

            if(empty($course)){
                 return array('hasProd'=>'false',"prodName"=>"此商品不存在");
            }else {

                 return array('hasProd'=>'true',"prodName"=>$course['title'].',￥'.$course['price']);
            }
           
        }
       
        if($offsetting['prodType']=='activity')
        {
            $activity = $this->getActivityService()->getActivity($offsetting['prodId']);
            if(empty($activity)){
                return array('hasProd'=>'false',"prodName"=>"此商品不存在");
            }else{
                 return array('hasProd'=>'true',"prodName"=>$activity['title']);
            }
        }

        return array('hasProd'=>'false',"prodName"=>"此商品不存在");



    }


    public function isValiable($offsale,$prodId){

        if (empty($offsale)) {
            return "该优惠码不存在，注意区分大小写哦";
        }

        if("无效" == $offsale['valid'] or "停用" == $offsale['valid']){
            return "该优惠码已被停用";
        }

        if("不可以" == $offsale['reuse']){

            $orders = $this->getOrderService()->getOrdersByPromoCode($offsale['promoCode']);

            foreach ($orders as $order) {
                if ("paid"==$order['status'])
                {
                    return "该优惠码已被使用";
                }
            }            
                
        }

        if(empty($offsale['validTime'])?false:time() > $offsale['validTime']){
            return "该优惠码已过期";
        }

        if($offsale['prodId'] != $prodId){
            return "该优惠码不适用于该商品";
        }
        return "success";
    }


    public function generateOffSaleCode($promoPrefix)
    {
        return  date('ymd', time()).$promoPrefix.$this->generateChars(8);
    }

    private function generateChars( $length = 8 ) {  
        // 密码字符集，可任意添加你需要的字符  
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password ="";  
        for ( $i = 0; $i < $length; $i++ )  
        {  
        // 这里提供两种字符获取方式  
        // 第一种是使用 substr 截取$chars中的任意一位字符；  
        // 第二种是取字符数组 $chars 的任意元素  
        // $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);  
        $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
        }  
        return $password;  
    }


    private function getOffSaleDao()
    {
        return $this->createDao('Sale.OffSaleDao');
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


class OffSaleSerialize
{

     //将php对象变成数据库字段。。。数组变为以|连接的字符串,时间字符串变成时间戳数字。。。。
    public static function serialize(array &$offsale)
    {
       
        if (isset($offsale['strvalidTime'])) {
            if (!empty($offsale['strvalidTime'])) {
                $offsale['validTime'] = strtotime($offsale['strvalidTime']);
            }
        }
        unset($offsale['strvalidTime']);


        return $offsale;
    }

    //将数据库字段变成php对象。。。以|连接的字符串变为数组,时间戳数字变成时间字符串。。。。

    public static function unserialize(array $offsale = null)
    {
        if (empty($offsale)) {
            return $offsale;
        }


        if(empty($offsale['validTime'])){
            $offsale['validTime']='';
        }else{
            $offsale['validTimeNum']=$offsale['validTime'];
            $offsale['validTime']=date("Y-m-d H:i",$offsale['validTime']);
        }

        return $offsale;
    }

    public static function unserializes(array $offsales)
    {
        return array_map(function($offsale) {
            return OffSaleSerialize::unserialize($offsale);
        }, $offsales);
    }
}