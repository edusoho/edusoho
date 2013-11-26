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



    }

    public function createOffsales($offsaleSetting){

        
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


    private function generateOffsaleCode($order)
    {
        return  'CF' . date('YmdHis', time()) . mt_rand(10000,99999);
    }


    private function getOffsaleDao()
    {
        return $this->createDao('Sale.OffsaleDao');
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
