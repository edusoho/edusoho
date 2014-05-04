<?php
namespace Topxia\Service\State\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\State\PartnerStateService;
use Topxia\Common\ArrayToolkit;

class PartnerStateServiceImpl extends BaseService implements PartnerStateService
{

    public function findPartnerStatesByIds(array $ids)
    {
        $partnerStates =  PartnerStateSerialize::unserializes(
             $this->getPartnerStateDao()->findPartnerStatesByIds($ids)
        );

        return ArrayToolkit::index($partnerStates, 'id');
    }

    public function getPartnerState($id)
    {
        return PartnerStateSerialize::unserialize($this->getPartnerStateDao()->getPartnerState($id));
    }
   

    public function searchPartnerStates($conditions, $sort = 'latest', $start, $limit)
    {
        $conditions = $this->_preparePartnerStateConditions($conditions);
        if ($sort == 'latest') {
            $orderBy = array('createdTime', 'DESC');
        }
        
        return PartnerStateSerialize::unserializes($this->getPartnerStateDao()->searchPartnerStates($conditions, $orderBy, $start, $limit));
    }


    public function searchPartnerStateCount($conditions)
    {
        $conditions = $this->_preparePartnerStateConditions($conditions);
        return $this->getPartnerStateDao()->searchPartnerStateCount($conditions);
    }

    private function _preparePartnerStateConditions($conditions)
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


    public function createPartnerState($partnerState){

        $partnerState['createdTime'] = time();

        $partnerState = $this->getPartnerStateDao()->addPartnerState(PartnerStateSerialize::serialize($partnerState));

        return $this->getPartnerState($partnerState['id']);

    }

    public function updatePartnerState($id, $partnerState)
    {

        return $this->getPartnerStateDao()->updatePartnerState($id, PartnerStateSerialize::serialize($partnerState));
    }

    public function deletePartnerStates(array $ids)
    {
        foreach ($ids as $id) {
            $this->getPartnerStateDao()->deletePartnerState($id);
        }
    }

    public function deleteByDate($date)
    {

        return $this->getPartnerStateDao()->deleteByDate($date);
    }

    private function getPartnerStateDao()
    {
        return $this->createDao('State.PartnerStateDao');
    }
   


}


class PartnerStateSerialize
{

     //将php对象变成数据库字段。。。数组变为以|连接的字符串,时间字符串变成时间戳数字。。。。
    public static function serialize(array &$partnerState)
    {
       
        // if (isset($partnerState['strvalidTime'])) {
        //     if (!empty($partnerState['strvalidTime'])) {
        //         $partnerState['validTime'] = strtotime($partnerState['strvalidTime']);
        //     }
        //     unset($partnerState['strvalidTime']);
        // }
       


        return $partnerState;
    }

    //将数据库字段变成php对象。。。以|连接的字符串变为数组,时间戳数字变成时间字符串。。。。

    public static function unserialize(array $partnerState = null)
    {
        if (empty($partnerState)) {
            return $partnerState;
        }


        // if(empty($partnerState['validTime'])){
        //     $partnerState['validTime']='';
        // }else{
        //     $partnerState['validTimeNum']=$partnerState['validTime'];
        //     $partnerState['validTime']=date("Y-m-d H:i",$partnerState['validTime']);
        // }

        return $partnerState;
    }

    public static function unserializes(array $partnerStates)
    {
        return array_map(function($partnerState) {
            return PartnerStateSerialize::unserialize($partnerState);
        }, $partnerStates);
    }
}