<?php
namespace Topxia\Service\AD\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Ad\SettingService;
use Topxia\Common\ArrayToolkit;

class SettingServiceImpl extends BaseService implements SettingService
{

    public function findSettingsByIds(array $ids)
    {
        $settings =  SettingSerialize::unserializes(
             $this->getSettingDao()->findSettingsByIds($ids)
        );

        return ArrayToolkit::index($settings, 'id');
    }

    public function getSetting($id)
    {
        return SettingSerialize::unserialize($this->getSettingDao()->getSetting($id));
    }

    public function findSettingByTargetUrl($targetUrl)
    {
        return SettingSerialize::unserialize($this->getSettingDao()->findSettingByTargetUrl($targetUrl));
    }

    public function searchSettings($conditions, $sort = 'latest', $start, $limit)
    {
        $conditions = $this->_prepareSettingConditions($conditions);
        if ($sort == 'popular') {
            $orderBy =  array('hitNum', 'DESC');
        } else if ($sort == 'recommended') {
            $orderBy = array('recommendedTime', 'DESC');
        } else {
            $orderBy = array('createdTime', 'DESC');
        }
        
        return SettingSerialize::unserializes($this->getSettingDao()->searchSettings($conditions, $orderBy, $start, $limit));
    }


    public function searchSettingCount($conditions)
    {
        $conditions = $this->_prepareSettingConditions($conditions);
        return $this->getSettingDao()->searchSettingCount($conditions);
    }

    private function _prepareSettingConditions($conditions)
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


    public function createSetting($setting){

        $setting = ArrayToolkit::parts($setting, array('id','name','targetUrl','showUrl','showMode','showWhen','showWait','scope','status','hits','userId','createdTime','updatedTime','publishedTime'));

        $setting['createdTime']=time();

        $setting['userId'] = $this->getCurrentUser()->id;

        $setting = $this->getSettingDao()->addSetting(SettingSerialize::serialize($setting));

        return $this->getSetting($setting['id']);

    }

    public function updateSetting($id, $setting)
    {

        $setting = ArrayToolkit::parts($setting, array('id','name','targetUrl','showUrl','showMode','showWhen','showWait','scope','status','hits','userId','createdTime','updatedTime','publishedTime'));


        return $this->getSettingDao()->updateSetting($id, SettingSerialize::serialize($setting));
    }

    public function deleteSettings(array $ids)
    {
        foreach ($ids as $id) {
            $this->getSettingDao()->deleteSetting($id);
        }
    }

    private function getSettingDao()
    {
        return $this->createDao('Ad.SettingDao');
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


class SettingSerialize
{

     //将php对象变成数据库字段。。。数组变为以|连接的字符串,时间字符串变成时间戳数字。。。。
    public static function serialize(array &$setting)
    {
       
        if (isset($setting['strvalidTime'])) {
            if (!empty($setting['strvalidTime'])) {
                $setting['validTime'] = strtotime($setting['strvalidTime']);
            }
            unset($setting['strvalidTime']);
        }
       


        return $setting;
    }

    //将数据库字段变成php对象。。。以|连接的字符串变为数组,时间戳数字变成时间字符串。。。。

    public static function unserialize(array $setting = null)
    {
        if (empty($setting)) {
            return $setting;
        }


        if(empty($setting['validTime'])){
            $setting['validTime']='';
        }else{
            $setting['validTimeNum']=$setting['validTime'];
            $setting['validTime']=date("Y-m-d H:i",$setting['validTime']);
        }

        return $setting;
    }

    public static function unserializes(array $settings)
    {
        return array_map(function($setting) {
            return SettingSerialize::unserialize($setting);
        }, $settings);
    }
}