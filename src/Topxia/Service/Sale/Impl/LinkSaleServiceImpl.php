<?php
namespace Topxia\Service\Sale\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Sale\LinkSaleService;
use Topxia\Common\ArrayToolkit;

class LinkSaleServiceImpl extends BaseService implements LinkSaleService
{

    public function findLinkSalesByIds(array $ids)
    {
        $linksales =  LinkSaleSerialize::unserializes(
             $this->getLinkSaleDao()->findLinkSalesByIds($ids)
        );

        return ArrayToolkit::index($linksales, 'id');
    }

    public function getLinkSale($id)
    {
        return LinkSaleSerialize::unserialize($this->getLinkSaleDao()->getLinkSale($id));
    }


    public function getCourseLinkSaleByProdAndUser($prodType,$prodId,$userId){

        return LinkSaleSerialize::unserialize($this->getLinkSaleDao()->getCourseLinkSaleByProdAndUser($prodType,$prodId,$userId));

    }


    public function getCourseLinkSalesByProdsAndUser($prodType,$prodIds,$userId){

        $linksales= LinkSaleSerialize::unserializes($this->getLinkSaleDao()->getCourseLinkSalesByProdsAndUser($prodType,$prodIds,$userId));

         return ArrayToolkit::index($linksales, 'prodId');


    }


    public function getLinkSaleByoUrl($saleType,$oUrl,$userId){

        return LinkSaleSerialize::unserialize($this->getLinkSaleDao()->getLinkSaleByoUrl($saleType,$oUrl,$userId));

    }


    public function getLinkSaleBymTookeen($mTookeen)
    {
        return LinkSaleSerialize::unserialize($this->getLinkSaleDao()->getLinkSaleBymTookeen($mTookeen));
    }


    public function searchLinkSales($conditions, $sort = 'latest', $start, $limit)
    {
        $conditions = $this->_prepareLinkSaleConditions($conditions);
        if ($sort == 'popular') {
            $orderBy =  array('hitNum', 'DESC');
        } else if ($sort == 'recommended') {
            $orderBy = array('recommendedTime', 'DESC');
        } else {
            $orderBy = array('createdTime', 'DESC');
        }
        
        return LinkSaleSerialize::unserializes($this->getLinkSaleDao()->searchLinkSales($conditions, $orderBy, $start, $limit));
    }


    public function searchLinkSaleCount($conditions)
    {
        $conditions = $this->_prepareLinkSaleConditions($conditions);
        return $this->getLinkSaleDao()->searchLinkSaleCount($conditions);
    }

    private function _prepareLinkSaleConditions($conditions)
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


    public function createLinkSale($linksale){

        $linksale = ArrayToolkit::parts($linksale, array('id','partnerIP','saleType','prodType','prodId','prodName','linkName','adCommissionType','adCommission','adCommissionDay','customized','reduceType','reducePrice', 'mTookeen', 'oUrl', 'tUrl', 'strvalidTime','validTime', 'partnerId', 'updatedTime','createdTime', 'managerId'));

        $linksale['createdTime']=time();

        $linksale['parnterIP'] = $this->getCurrentUser()->currentIp;

        $linksale = $this->getLinkSaleDao()->addLinkSale(LinkSaleSerialize::serialize($linksale));

        return $this->getLinkSale($linksale['id']);

    }

    public function updateLinkSale($id, $linksale)
    {

        $linksale = ArrayToolkit::parts($linksale, array('id','partnerIP','saleType','prodType','prodId','prodName','linkName','adCommissionType','adCommission','adCommissionDay','customized','reduceType','reducePrice', 'mTookeen', 'oUrl', 'tUrl', 'strvalidTime','validTime', 'partnerId', 'updatedTime','createdTime', 'managerId'));

       return $this->getLinkSaleDao()->updateLinkSale($id, LinkSaleSerialize::serialize($linksale));
    }

    public function deleteLinkSales(array $ids)
    {
        foreach ($ids as $id) {
            $this->getLinkSaleDao()->deleteLinkSale($id);
        }
    }

    public function updateCourseLinkSale4unCustomized($adCommissionType,$adCommission,$adCommissionDay,$courseId)
    {

        return $this->getLinkSaleDao()->updateCourseLinkSale4unCustomized($adCommissionType,$adCommission,$adCommissionDay,$courseId);
    }


    public function generateLinkSaleTookeen($tookeenPrefix='')
    {
        return  $tookeenPrefix.date('ymd', time()).$this->generateChars(6);
    }


    private function generateChars( $length = 6 ) {  
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

    private function getLinkSaleDao()
    {
        return $this->createDao('Sale.LinkSaleDao');
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
        return $this->createService('Order.OrderService');
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


class LinkSaleSerialize
{

     //将php对象变成数据库字段。。。数组变为以|连接的字符串,时间字符串变成时间戳数字。。。。
    public static function serialize(array &$linksale)
    {
       
        if (isset($linksale['strvalidTime'])) {
            if (!empty($linksale['strvalidTime'])) {
                $linksale['validTime'] = strtotime($linksale['strvalidTime']);
            }
        }
        unset($linksale['strvalidTime']);


        return $linksale;
    }

    //将数据库字段变成php对象。。。以|连接的字符串变为数组,时间戳数字变成时间字符串。。。。

    public static function unserialize(array $linksale = null)
    {
        if (empty($linksale)) {
            return $linksale;
        }


        if(empty($linksale['validTime'])){
            $linksale['validTime']='';
        }else{
            $linksale['validTimeNum']=$linksale['validTime'];
            $linksale['validTime']=date("Y-m-d H:i",$linksale['validTime']);
        }

        return $linksale;
    }

    public static function unserializes(array $linksales)
    {
        return array_map(function($linksale) {
            return LinkSaleSerialize::unserialize($linksale);
        }, $linksales);
    }
}