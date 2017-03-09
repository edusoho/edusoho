<?php

namespace AppBundle\Controller\Callback\Resource\CloudSearch;

use AppBundle\Controller\Callback\Resource\BaseResource;

/**
 * 单个课程资源(对应course_set表).
 */
class Course extends BaseResource
{
    public function filter($res)
    {
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['updatedTime'] = date('c', $res['updatedTime']);

        $defaultSetting = $this->getSettingService()->get('default', array());
        foreach (array('smallPicture', 'middlePicture', 'largePicture') as $key) {
            if (empty($res[$key])) {
                $res[$key] = !isset($defaultSetting['course.png']) ? '' : $defaultSetting['course.png'];
            }
            $res[$key] = $this->getFileUrl($res[$key]);
        }

        return $res;
    }

     /**
      * @return Biz\System\Service\SettingService
      */
     protected function getSettingService()
     {
         return $this->getBiz()->service('System:SettingService');
     }
}
