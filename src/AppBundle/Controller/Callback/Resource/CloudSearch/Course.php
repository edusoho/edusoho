<?php

namespace AppBundle\Controller\Callback\Resource\CloudSearch;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\Callback\Resource\BaseResource;
use Topxia\Api\Util\TagUtil;

class Course extends BaseResource
{

    public function filter($res)
    {
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['updatedTime'] = date('c', $res['updatedTime']);
        $default            = $this->getSettingService()->get('default', array());

        if (empty($res['smallPicture']) && empty($res['middlePicture']) && empty($res['largePicture'])) {
            $res['smallPicture']  = !isset($default['course.png']) ? '' : $default['course.png'];
            $res['middlePicture'] = !isset($default['course.png']) ? '' : $default['course.png'];
            $res['largePicture']  = !isset($default['course.png']) ? '' : $default['course.png'];
        }

        foreach (array('smallPicture', 'middlePicture', 'largePicture') as $key) {
            $res[$key] = $this->getFileUrl($res[$key]);
        }

        // $res['tags'] = TagUtil::buildTags('course', $res['id']);
        // $res['tags'] = ArrayToolkit::column($res['tags'], 'name');

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