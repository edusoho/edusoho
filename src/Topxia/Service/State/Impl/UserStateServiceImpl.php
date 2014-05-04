<?php
namespace Topxia\Service\State\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\State\UserStateService;
use Topxia\Common\ArrayToolkit;

class UserStateServiceImpl extends BaseService implements UserStateService
{

    public function findUserStatesByIds(array $ids)
    {
        $userStates =  UserStateSerialize::unserializes(
             $this->getUserStateDao()->findUserStatesByIds($ids)
        );

        return ArrayToolkit::index($userStates, 'id');
    }

    public function getUserState($id)
    {
        return UserStateSerialize::unserialize($this->getUserStateDao()->getUserState($id));
    }
   

    public function searchUserStates($conditions, $sort = 'latest', $start, $limit)
    {
        $conditions = $this->_prepareUserStateConditions($conditions);
        if ($sort == 'latest') {
            $orderBy = array('createdTime', 'DESC');
        }
        
        return UserStateSerialize::unserializes($this->getUserStateDao()->searchUserStates($conditions, $orderBy, $start, $limit));
    }


    public function searchUserStateCount($conditions)
    {
        $conditions = $this->_prepareUserStateConditions($conditions);
        return $this->getUserStateDao()->searchUserStateCount($conditions);
    }

    private function _prepareUserStateConditions($conditions)
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


    public function createUserState($userState){

        $userState['createdTime'] = time();

        $userState = $this->getUserStateDao()->addUserState(UserStateSerialize::serialize($userState));

        return $this->getUserState($userState['id']);

    }

    public function updateUserState($id, $userState)
    {

        return $this->getUserStateDao()->updateUserState($id, UserStateSerialize::serialize($userState));
    }

    public function deleteUserStates(array $ids)
    {
        foreach ($ids as $id) {
            $this->getUserStateDao()->deleteUserState($id);
        }
    }

    public function deleteByDate($date)
    {

        return $this->getUserStateDao()->deleteByDate($date);
    }

    private function getUserStateDao()
    {
        return $this->createDao('State.UserStateDao');
    }
   


}


class UserStateSerialize
{

     //将php对象变成数据库字段。。。数组变为以|连接的字符串,时间字符串变成时间戳数字。。。。
    public static function serialize(array &$userState)
    {
       
        // if (isset($userState['strvalidTime'])) {
        //     if (!empty($userState['strvalidTime'])) {
        //         $userState['validTime'] = strtotime($userState['strvalidTime']);
        //     }
        //     unset($userState['strvalidTime']);
        // }
       


        return $userState;
    }

    //将数据库字段变成php对象。。。以|连接的字符串变为数组,时间戳数字变成时间字符串。。。。

    public static function unserialize(array $userState = null)
    {
        if (empty($userState)) {
            return $userState;
        }


        // if(empty($userState['validTime'])){
        //     $userState['validTime']='';
        // }else{
        //     $userState['validTimeNum']=$userState['validTime'];
        //     $userState['validTime']=date("Y-m-d H:i",$userState['validTime']);
        // }

        return $userState;
    }

    public static function unserializes(array $userStates)
    {
        return array_map(function($userState) {
            return UserStateSerialize::unserialize($userState);
        }, $userStates);
    }
}