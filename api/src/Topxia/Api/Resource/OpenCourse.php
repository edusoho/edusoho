<?php


namespace Topxia\Api\Resource;


class OpenCourse extends BaseResource
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

        return $res;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}