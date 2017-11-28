<?php

namespace AppBundle\Controller\Callback\CloudFile;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class Files extends BaseController
{
    public function notify(Request $request)
    {
        $token = $request->query->get('token');

        $userToken = $this->getTokenService()->verifyToken('mp4_delete.callback', $token);

        if (!$userToken) {
            throw $this->createAccessDeniedException('token error');
        }

        $setting = $this->getSettingService()->get('storage', array());
        $setting['delete_mp4_status'] = 'finished';
        $this->getSettingService()->set('storage', $setting);

        $this->getNotifiactionService()->notify($userToken['userId'], 'delete-cloud-mp4', array());

        return true;
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getNotifiactionService()
    {
        return $this->createService('User:NotificationService');
    }
}
