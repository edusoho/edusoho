<?php
namespace Topxia\Service\State\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\State\BusinessStateService;
use Topxia\Common\ArrayToolkit;

class BusinessStateServiceImpl extends BaseService implements BusinessStateService
{

    public function findBusinessStatesByIds(array $ids)
    {
        $businessStates =  BusinessStateSerialize::unserializes(
             $this->getBusinessStateDao()->findBusinessStatesByIds($ids)
        );

        return ArrayToolkit::index($businessStates, 'id');
    }

    public function getBusinessState($id)
    {
        return BusinessStateSerialize::unserialize($this->getBusinessStateDao()->getBusinessState($id));
    }
   

    public function searchBusinessStates($conditions, $sort = 'latest', $start, $limit)
    {
        $conditions = $this->_prepareBusinessStateConditions($conditions);
        if ($sort == 'latest') {
            $orderBy = array('createdTime', 'DESC');
        }
        
        return BusinessStateSerialize::unserializes($this->getBusinessStateDao()->searchBusinessStates($conditions, $orderBy, $start, $limit));
    }


    public function searchBusinessStateCount($conditions)
    {
        $conditions = $this->_prepareBusinessStateConditions($conditions);
        return $this->getBusinessStateDao()->searchBusinessStateCount($conditions);
    }

    private function _prepareBusinessStateConditions($conditions)
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


    public function createBusinessState($businessState){

        $businessState['createdTime'] = time();

        $businessState = $this->getBusinessStateDao()->addBusinessState(BusinessStateSerialize::serialize($businessState));

        return $this->getBusinessState($businessState['id']);

    }

    public function updateBusinessState($id, $businessState)
    {

        return $this->getBusinessStateDao()->updateBusinessState($id, BusinessStateSerialize::serialize($businessState));
    }

    public function deleteBusinessStates(array $ids)
    {
        foreach ($ids as $id) {
            $this->getBusinessStateDao()->deleteBusinessState($id);
        }
    }

   public function deleteByDate($date,$prodType,$prodId)
    {

        return $this->getBusinessStateDao()->deleteByDate($date,$prodType,$prodId);
    }


    private function getBusinessStateDao()
    {
        return $this->createDao('State.BusinessStateDao');
    }
   


}


class BusinessStateSerialize
{

     //将php对象变成数据库字段。。。数组变为以|连接的字符串,时间字符串变成时间戳数字。。。。
    public static function serialize(array &$businessState)
    {
       
        // if (isset($businessState['strvalidTime'])) {
        //     if (!empty($businessState['strvalidTime'])) {
        //         $businessState['validTime'] = strtotime($businessState['strvalidTime']);
        //     }
        //     unset($businessState['strvalidTime']);
        // }
       


        return $businessState;
    }

    //将数据库字段变成php对象。。。以|连接的字符串变为数组,时间戳数字变成时间字符串。。。。

    public static function unserialize(array $businessState = null)
    {
        if (empty($businessState)) {
            return $businessState;
        }


        // if(empty($businessState['validTime'])){
        //     $businessState['validTime']='';
        // }else{
        //     $businessState['validTimeNum']=$businessState['validTime'];
        //     $businessState['validTime']=date("Y-m-d H:i",$businessState['validTime']);
        // }

        return $businessState;
    }

    public static function unserializes(array $businessStates)
    {
        return array_map(function($businessState) {
            return BusinessStateSerialize::unserialize($businessState);
        }, $businessStates);
    }
}