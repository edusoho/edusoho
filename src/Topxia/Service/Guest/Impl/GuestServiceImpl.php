<?php
namespace Topxia\Service\Guest\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Guest\GuestService;
use Topxia\Common\ArrayToolkit;

class GuestServiceImpl extends BaseService implements GuestService
{

    public function findGuestsByIds(array $ids)
    {
        $guests =  GuestSerialize::unserializes(
             $this->getGuestDao()->findGuestsByIds($ids)
        );

        return ArrayToolkit::index($guests, 'id');
    }

    public function getGuest($id)
    {
        return GuestSerialize::unserialize($this->getGuestDao()->getGuest($id));
    }

    public function getGuestBymTookeen($mTookeen)
    {
        return GuestSerialize::unserialize($this->getGuestDao()->getGuestBymTookeen($mTookeen));
    }


    public function getGuestByUserId($userId)
    {
        return GuestSerialize::unserialize($this->getGuestDao()->getGuestByUserId($userId));
    }

    public function searchGuests($conditions, $sort = 'latest', $start, $limit)
    {
        $conditions = $this->_prepareGuestConditions($conditions);
        if ($sort == 'popular') {
            $orderBy =  array('hitNum', 'DESC');
        } else if ($sort == 'recommended') {
            $orderBy = array('recommendedTime', 'DESC');
        } else {
            $orderBy = array('createdTime', 'DESC');
        }
        
        return GuestSerialize::unserializes($this->getGuestDao()->searchGuests($conditions, $orderBy, $start, $limit));
    }


    public function searchGuestCount($conditions)
    {
        $conditions = $this->_prepareGuestConditions($conditions);
        return $this->getGuestDao()->searchGuestCount($conditions);
    }

    private function _prepareGuestConditions($conditions)
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


    public function createGuest($guest){

        $guest = ArrayToolkit::parts($guest, array('id','userId','lastAccessTime','lastAccessIp','lastAccessmTookeen','lastAccessPartnerId','createdTime','createdIp','createdmTookeen','createdPartnerId'));

        $guest['createdTime']=time();

        $guest['createdIp'] = $this->getCurrentUser()->currentIp;

        $guest['userId'] = $this->getCurrentUser()->id;

        $guest['lastAccessTime']=time();

        $guest['lastAccessIp'] = $this->getCurrentUser()->currentIp;

        $guest = $this->getGuestDao()->addGuest(GuestSerialize::serialize($guest));

        return $this->getGuest($guest['id']);

    }

    public function updateGuest($id, $guest)
    {

         $guest = ArrayToolkit::parts($guest, array('id','userId','lastAccessTime','lastAccessIp','lastAccessmTookeen','lastAccessPartnerId','createdTime','createdIp','createdmTookeen','createdPartnerId'));

       return $this->getGuestDao()->updateGuest($id, GuestSerialize::serialize($guest));
    }

    public function deleteGuests(array $ids)
    {
        foreach ($ids as $id) {
            $this->getGuestDao()->deleteGuest($id);
        }
    }

    

    private function getGuestDao()
    {
        return $this->createDao('Guest.GuestDao');
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


class GuestSerialize
{

     //将php对象变成数据库字段。。。数组变为以|连接的字符串,时间字符串变成时间戳数字。。。。
    public static function serialize(array &$guest)
    {
       
        if (isset($guest['strvalidTime'])) {
            if (!empty($guest['strvalidTime'])) {
                $guest['validTime'] = strtotime($guest['strvalidTime']);
            }
            unset($guest['strvalidTime']);
        }
       


        return $guest;
    }

    //将数据库字段变成php对象。。。以|连接的字符串变为数组,时间戳数字变成时间字符串。。。。

    public static function unserialize(array $guest = null)
    {
        if (empty($guest)) {
            return $guest;
        }


        if(empty($guest['validTime'])){
            $guest['validTime']='';
        }else{
            $guest['validTimeNum']=$guest['validTime'];
            $guest['validTime']=date("Y-m-d H:i",$guest['validTime']);
        }

        return $guest;
    }

    public static function unserializes(array $guests)
    {
        return array_map(function($guest) {
            return GuestSerialize::unserialize($guest);
        }, $guests);
    }
}