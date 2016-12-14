<?php

namespace Topxia\Api\Resource;

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

        $res['convNo'] = $this->getConversation($res['id']);

        //temp fix for app, will be remove when new app version published
        $res['expiryDay'] = '0';

        $res['tags'] = TagUtil::buildTags('course', $res['id']);

        return $res;
    }

    public function simplify($res)
    {
        $simple = array();

        $simple['id']      = $res['id'];
        $simple['title']   = $res['title'];
        $simple['picture'] = $this->getFileUrl($res['smallPicture']);
        $simple['convNo']  = $this->getConversation($res['id']);

        return $simple;
    }

    protected function getConversation($courseId)
    {
        $conversation = $this->getConversationService()->getConversationByTarget($courseId, 'course');
        if ($conversation) {
            return $conversation['no'];
        }

        return '';
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }
}
