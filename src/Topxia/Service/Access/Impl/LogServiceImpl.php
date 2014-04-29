<?php
namespace Topxia\Service\Access\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Access\LogService;
use Topxia\Common\ArrayToolkit;

class LogServiceImpl extends BaseService implements LogService
{

    public function findLogsByIds(array $ids)
    {
        $settings =  LogSerialize::unserializes(
             $this->getLogDao()->findLogsByIds($ids)
        );

        return ArrayToolkit::index($settings, 'id');
    }

    public function getLog($id)
    {
        return LogSerialize::unserialize($this->getLogDao()->getLog($id));
    }

    public function searchLogs($conditions, $sort = 'latest', $start, $limit)
    {
        $conditions = $this->_prepareLogConditions($conditions);
        if ($sort == 'popular') {
            $orderBy =  array('hitNum', 'DESC');
        } else if ($sort == 'recommended') {
            $orderBy = array('recommendedTime', 'DESC');
        } else {
            $orderBy = array('createdTime', 'DESC');
        }
        
        return LogSerialize::unserializes($this->getLogDao()->searchLogs($conditions, $orderBy, $start, $limit));
    }


    public function searchLogCount($conditions)
    {
        $conditions = $this->_prepareLogConditions($conditions);
        return $this->getLogDao()->searchLogCount($conditions);
    }

    private function _prepareLogConditions($conditions)
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


    public function createLog($log){

       $log = ArrayToolkit::parts($log, array('id','guestId','userId','prodType','prodName','prodId','accessHref','accessPathName','accessSearch','createdTime','createdIp','mTookeen','partnerId'));

        $log['createdTime']=time();

        $log['createdIp'] = $this->getCurrentUser()->currentIp;

        $log['userId'] =$this->getCurrentUser()->id;

        $log = $this->getLogDao()->addLog(LogSerialize::serialize($log));

        return $this->getLog($log['id']);

    }

    public function updateLog($id, $log)
    {

        $log = ArrayToolkit::parts($log, array('id','guestId','userId','prodType','prodName','prodId','accessHref','accessPathName','accessSearch','createdTime','createdIp','mTookeen','partnerId'));


        return $this->getLogDao()->updateLog($id, LogSerialize::serialize($log));
    }

    public function deleteLogs(array $ids)
    {
        foreach ($ids as $id) {
            $this->getLogDao()->deleteLog($id);
        }
    }

    private function getLogDao()
    {
        return $this->createDao('Access.LogDao');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

}


class LogSerialize
{

     //将php对象变成数据库字段。。。数组变为以|连接的字符串,时间字符串变成时间戳数字。。。。
    public static function serialize(array &$log)
    {
       
        if (isset($log['strvalidTime'])) {
            if (!empty($log['strvalidTime'])) {
                $log['validTime'] = strtotime($log['strvalidTime']);
            }
            unset($log['strvalidTime']);
        }
       


        return $log;
    }

    //将数据库字段变成php对象。。。以|连接的字符串变为数组,时间戳数字变成时间字符串。。。。

    public static function unserialize(array $log = null)
    {
        if (empty($log)) {
            return $log;
        }


        if(empty($log['validTime'])){
            $log['validTime']='';
        }else{
            $log['validTimeNum']=$log['validTime'];
            $log['validTime']=date("Y-m-d H:i",$log['validTime']);
        }

        return $log;
    }

    public static function unserializes(array $settings)
    {
        return array_map(function($log) {
            return LogSerialize::unserialize($log);
        }, $settings);
    }
}