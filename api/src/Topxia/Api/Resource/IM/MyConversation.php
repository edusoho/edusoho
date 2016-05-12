<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class MyConversation extends BaseResource
{
    public function post(Application $app, Request $request, $no)
    {
        $requiredFields = array('updatedTime');

        $fields = $this->checkRequiredFields($requiredFields, $request->request->all());

        $myConversation = $this->getMyConversationService()->updateMyConversationByNo($no, $fields['updatedTime']);

        return $this->filter($myConversation);
    }

    public function filter($res)
    {
        if (!empty($res['createdTime'])) {
            $res['createdTime'] = date('c', $res['createdTime']);
        }
        if (!empty($res['updatedTime'])) {
            $res['updatedTime'] = date('c', $res['updatedTime']);
        }
        return $res;
    }


    protected function getMyConversationService()
    {
        return $this->getServiceKernel()->createService('IM.MyConversationService');
    }
}
