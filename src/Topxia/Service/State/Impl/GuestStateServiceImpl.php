<?php
namespace Topxia\Service\State\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\State\GuestStateService;
use Topxia\Common\ArrayToolkit;

class GuestStateServiceImpl extends BaseService implements GuestStateService
{

    public function findGuestStatesByIds(array $ids)
    {
        $guestStates =  GuestStateSerialize::unserializes(
             $this->getGuestStateDao()->findGuestStatesByIds($ids)
        );

        return ArrayToolkit::index($guestStates, 'id');
    }

    public function getGuestState($id)
    {
        return GuestStateSerialize::unserialize($this->getGuestStateDao()->getGuestState($id));
    }
   

    public function searchGuestStates($conditions, $sort = 'latest', $start, $limit)
    {
        $conditions = $this->_prepareGuestStateConditions($conditions);
        if ($sort == 'latest') {
            $orderBy = array('createdTime', 'DESC');
        }
        
        return GuestStateSerialize::unserializes($this->getGuestStateDao()->searchGuestStates($conditions, $orderBy, $start, $limit));
    }


    public function searchGuestStateCount($conditions)
    {
        $conditions = $this->_prepareGuestStateConditions($conditions);
        return $this->getGuestStateDao()->searchGuestStateCount($conditions);
    }

    private function _prepareGuestStateConditions($conditions)
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


    public function createGuestState($guestState){

        $guestState['createdTime'] = time();

        $guestState = $this->getGuestStateDao()->addGuestState(GuestStateSerialize::serialize($guestState));

        return $this->getGuestState($guestState['id']);

    }

    public function updateGuestState($id, $guestState)
    {

        return $this->getGuestStateDao()->updateGuestState($id, GuestStateSerialize::serialize($guestState));
    }

    public function deleteGuestStates(array $ids)
    {
        foreach ($ids as $id) {
            $this->getGuestStateDao()->deleteGuestState($id);
        }
    }

    public function deleteByDate($date)
    {

        return $this->getGuestStateDao()->deleteByDate($date);
    }

    private function getGuestStateDao()
    {
        return $this->createDao('State.GuestStateDao');
    }
   


}


class GuestStateSerialize
{

     //将php对象变成数据库字段。。。数组变为以|连接的字符串,时间字符串变成时间戳数字。。。。
    public static function serialize(array &$guestState)
    {
       
        // if (isset($guestState['strvalidTime'])) {
        //     if (!empty($guestState['strvalidTime'])) {
        //         $guestState['validTime'] = strtotime($guestState['strvalidTime']);
        //     }
        //     unset($guestState['strvalidTime']);
        // }
       


        return $guestState;
    }

    //将数据库字段变成php对象。。。以|连接的字符串变为数组,时间戳数字变成时间字符串。。。。

    public static function unserialize(array $guestState = null)
    {
        if (empty($guestState)) {
            return $guestState;
        }


        // if(empty($guestState['validTime'])){
        //     $guestState['validTime']='';
        // }else{
        //     $guestState['validTimeNum']=$guestState['validTime'];
        //     $guestState['validTime']=date("Y-m-d H:i",$guestState['validTime']);
        // }

        return $guestState;
    }

    public static function unserializes(array $guestStates)
    {
        return array_map(function($guestState) {
            return GuestStateSerialize::unserialize($guestState);
        }, $guestStates);
    }
}