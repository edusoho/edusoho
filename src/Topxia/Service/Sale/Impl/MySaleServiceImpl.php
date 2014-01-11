<?php
namespace Topxia\Service\Sale\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Sale\MySaleService;
use Topxia\Common\ArrayToolkit;

class MySaleServiceImpl extends BaseService implements MySaleService
{

    public function findMySalesByIds(array $ids)
    {
        $mysales =  MySaleSerialize::unserializes(
             $this->getMySaleDao()->findMySalesByIds($ids)
        );

        return ArrayToolkit::index($mysales, 'id');
    }

    public function getMySale($id)
    {
        return MySaleSerialize::unserialize($this->getMySaleDao()->getMySale($id));
    }


    public function getMySaleByProdAndUser($prodType,$prodId,$userId){

        return MySaleSerialize::unserialize($this->getMySaleDao()->getMySaleByProdAndUser($prodType,$prodId,$userId));

    }


    public function getMySaleBymTookeen($mTookeen)
    {
        return MySaleSerialize::unserialize($this->getMySaleDao()->getMySaleBymTookeen($mTookeen));
    }


    public function searchMySales($conditions, $sort = 'latest', $start, $limit)
    {
        $conditions = $this->_prepareMySaleConditions($conditions);
        if ($sort == 'popular') {
            $orderBy =  array('hitNum', 'DESC');
        } else if ($sort == 'recommended') {
            $orderBy = array('recommendedTime', 'DESC');
        } else {
            $orderBy = array('createdTime', 'DESC');
        }
        
        return MySaleSerialize::unserializes($this->getMySaleDao()->searchMySales($conditions, $orderBy, $start, $limit));
    }


    public function searchMySaleCount($conditions)
    {
        $conditions = $this->_prepareMySaleConditions($conditions);
        return $this->getMySaleDao()->searchMySaleCount($conditions);
    }

    private function _prepareMySaleConditions($conditions)
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


    public function createMySale($mysale){

        $mysale = ArrayToolkit::parts($mysale, array('id', 'prodType','prodId','prodName','commission', 'mTookeen', 'tUrl', 'validTime', 'userId', 'updatedTime','createdTime', 'managerId'));

        $mysale['createdTime']=time();

        $mysale = $this->getMySaleDao()->addMySale(MySaleSerialize::serialize($mysale));

        return $this->getMySale($mysale['id']);

    }


    public function deleteMySales(array $ids)
    {
        foreach ($ids as $id) {
            $this->getMySaleDao()->deleteMySale($id);
        }
    }


    public function generateMySaleTookeen($tookeenPrefix='')
    {
        return  $tookeenPrefix.$this->generateChars(24);
    }


    private function generateChars( $length = 24 ) {  
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

    private function getMySaleDao()
    {
        return $this->createDao('Sale.MySaleDao');
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


class MySaleSerialize
{

     //将php对象变成数据库字段。。。数组变为以|连接的字符串,时间字符串变成时间戳数字。。。。
    public static function serialize(array &$mysale)
    {
       
        if (isset($mysale['strvalidTime'])) {
            if (!empty($mysale['strvalidTime'])) {
                $mysale['validTime'] = strtotime($mysale['strvalidTime']);
            }
        }
        unset($mysale['strvalidTime']);


        return $mysale;
    }

    //将数据库字段变成php对象。。。以|连接的字符串变为数组,时间戳数字变成时间字符串。。。。

    public static function unserialize(array $mysale = null)
    {
        if (empty($mysale)) {
            return $mysale;
        }


        if(empty($mysale['validTime'])){
            $mysale['validTime']='';
        }else{
            $mysale['validTimeNum']=$mysale['validTime'];
            $mysale['validTime']=date("Y-m-d H:i",$mysale['validTime']);
        }

        return $mysale;
    }

    public static function unserializes(array $mysales)
    {
        return array_map(function($mysale) {
            return MySaleSerialize::unserialize($mysale);
        }, $mysales);
    }
}