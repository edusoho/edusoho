<?php
namespace Topxia\Service\Sale\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Sale\OffsaleService;
use Topxia\Common\ArrayToolkit;

class OffsaleServiceImpl extends BaseService implements OffsaleService
{

    public function findOffsalesByIds(array $ids)
    {
        $offsales =  OffsaleSerialize::unserializes(
             $this->getOffsaleDao()->findOffsalesByIds($ids)
        );

        return ArrayToolkit::index($offsales, 'id');
    }

    public function getOffsale($id)
    {
        return OffsaleSerialize::unserialize($this->getOffsaleDao()->getOffsale($id));
    }


    public function getOffsaleByCode($code)
    {
        return OffsaleSerialize::unserialize($this->getOffsaleDao()->getOffsaleByCode($code));
    }


    public function searchOffsales($conditions, $sort = 'latest', $start, $limit)
    {
        $conditions = $this->_prepareOffsaleConditions($conditions);
        if ($sort == 'popular') {
            $orderBy =  array('hitNum', 'DESC');
        } else if ($sort == 'recommended') {
            $orderBy = array('recommendedTime', 'DESC');
        } else {
            $orderBy = array('createdTime', 'DESC');
        }
        
        return OffsaleSerialize::unserializes($this->getOffsaleDao()->searchOffsales($conditions, $orderBy, $start, $limit));
    }


    public function searchOffsaleCount($conditions)
    {
        $conditions = $this->_prepareOffsaleConditions($conditions);
        return $this->getOffsaleDao()->searchOffsaleCount($conditions);
    }

    private function _prepareOffsaleConditions($conditions)
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


    public function createOffsale($offsale){

        $offsale = ArrayToolkit::parts($offsale, array('promoName', 'promoCode','reducePrice','prodType', 'prodName', 'prodId', 'reuse', 'valid', 'strvalidTime','validTime', 'createdTime', 'id'));

        $offsale['createdTime']=time();

        $offsale = $this->getOffsaleDao()->addOffsale(OffsaleSerialize::serialize($offsale));

        return $this->getOffsale($offsale['id']);

    }

    public function createOffsales($offsetting){

        if(empty($offsetting)){
            return 0;
        }

        if($offsetting['prodType']=='课程')
        {
            $course = $this->getCourseService()->getCourse($offsetting['prodId']);

            $offsetting['prodName'] = $course['title'];
        }

        if($offsetting['prodType']=='活动')
        {
            $activity = $this->getActivityService()->getActivity($offsetting['prodId']);
            $offsetting['prodName'] = $activity['title'];
        }

        for ($i = 1; $i<= $offsetting['promoNum']; $i++) {

            $offsale['prodType'] = $offsetting['prodType'];
            $offsale['prodName'] = $offsetting['prodName'];
            $offsale['prodId']  = $offsetting['prodId'];
            $offsale['promoName'] = $offsetting['promoName'];
            $offsale['promoCode']= $this->generateOffsaleCode($offsetting['promoPrefix']);
            $offsale['reducePrice'] = $offsetting['reducePrice'];
            $offsale['reuse']= $offsetting['reuse'];
            $offsale['valid']= '有效';
            $offsale['strvalidTime']= $offsetting['strvalidTime'];
           
            $this->createOffsale($offsale);        
        }

        
    }

    public function deleteOffsales(array $ids)
    {
        foreach ($ids as $id) {
            $this->getOffsaleDao()->deleteOffsale($id);
        }
    }

    public function checkProd($offsetting){

        if(empty($offsetting)){
            return "该商品不存在，请重新输入";
        }

        if($offsetting['prodType']=='课程')
        {
            $course = $this->getCourseService()->getCourse($offsetting['prodId']);

            if(empty($course)){
                return "该商品不存在，请重新输入";
            }
           
        }
       
        if($offsetting['prodType']=='活动')
        {
            $activity = $this->getActivityService()->getActivity($offsetting['prodId']);
            if(empty($activity)){
                return "该商品不存在，请重新输入";
            }
        }

         return "success";



    }


    public function isValiable($offsale,$prodId){

        if (empty($offsale)) {
            return "该优惠码不存在，注意区分大小写哦";
        }

        if("无效" == $offsale['valid']){
            return "该优惠码已被停用";
        }

        if("不可以" == $offsale['reuse']){

            $order = $this->getOrderService()->getOrderByPromocode($offsale['promoCode']);

            if (!empty($order))
            {
                return "该优惠码已被使用";
            }
                
        }

        if(empty($offsale['validTime'])?false:time() > $offsale['validTime']){
            return "该优惠码已过期";
        }

        if($offsale['prodId'] != $prodId){
            return "该优惠码不适用于该".$offsale['prodType'];
        }
        return "success";
    }


    private function generateOffsaleCode($promoPrefix)
    {
        return  $promoPrefix.$this->generateChars(8);
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


    private function getOffsaleDao()
    {
        return $this->createDao('Sale.OffsaleDao');
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


class OffsaleSerialize
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
            return OffsaleSerialize::unserialize($offsale);
        }, $offsales);
    }
}