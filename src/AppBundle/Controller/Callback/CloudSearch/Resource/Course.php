<?php

namespace AppBundle\Controller\Callback\CloudSearch\Resource;

use AppBundle\Controller\Callback\CloudSearch\BaseProvider;
use AppBundle\Common\ArrayToolkit;

/**
 * 单个课程资源(对应course_set表).
 */
class Course extends BaseProvider
{
    public function filter($res)
    {
        $defaultSetting = $this->getSettingService()->get('default', array());
        $defaultPicture = isset($defaultSetting['course.png']) ? $this->getFileUrl($defaultSetting['course.png']) : '';

        $filteredRes = array();
        $filteredRes['id'] = $res['id'];
        $filteredRes['title'] = $res['title'];
        $filteredRes['subtitle'] = $res['subtitle'];
        $filteredRes['type'] = $res['type'];
        $filteredRes['price'] = $res['minCoursePrice'];
        $filteredRes['lessonNum'] = $res['totalTaskNum'];
        $filteredRes['rating'] = $res['rating'];
        $filteredRes['ratingNum'] = $res['ratingNum'];
        $filteredRes['tags'] = ArrayToolkit::column($res['tags'], 'name');
        $filteredRes['category'] = isset($res['category']['name']) ? $res['category']['name'] : '';
        $filteredRes['about'] = $this->filterHtml($res['summary']);
        $filteredRes['goals'] = $res['goals'];
        $filteredRes['picture'] = isset($res['largePicture']) ? $this->getFileUrl($res['largePicture']) : $defaultPicture;
        $filteredRes['audiences'] = $res['audiences'];
        $filteredRes['hitNum'] = $res['hitNum'];
        $filteredRes['createdTime'] = date('c', $res['createdTime']);
        $filteredRes['updatedTime'] = date('c', $res['updatedTime']);

        return $filteredRes;
    }

     /**
      * @return Biz\System\Service\SettingService
      */
     protected function getSettingService()
     {
         return $this->getBiz()->service('System:SettingService');
     }
}
