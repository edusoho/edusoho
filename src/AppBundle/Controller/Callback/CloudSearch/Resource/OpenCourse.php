<?php

namespace AppBundle\Controller\Callback\CloudSearch\Resource;

use AppBundle\Controller\Callback\CloudSearch\BaseProvider;
use AppBundle\Common\ArrayToolkit;

class OpenCourse extends BaseProvider
{
    public function filter($res)
    {
        $filteredRes = array();
        $filteredRes['id'] = $res['id'];
        $filteredRes['title'] = $res['title'];
        $filteredRes['subtitle'] = $res['subtitle'];
        $filteredRes['type'] = 'public_'.$res['type'];
        $filteredRes['lessonNum'] = $res['lessonNum'];
        $filteredRes['studentNum'] = $res['studentNum'];
        $filteredRes['hitNum'] = $res['hitNum'];
        $filteredRes['likeNum'] = $res['likeNum'];
        $filteredRes['postNum'] = $res['postNum'];
        $filteredRes['tags'] = ArrayToolkit::column($res['tags'], 'name');
        $filteredRes['category'] = isset($res['category']['name']) ? $res['category']['name'] : '';
        $filteredRes['about'] = $res['about'];
        $filteredRes['picture'] = isset($res['largePicture']) ? $this->getFileUrl($res['largePicture']) : '';
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
