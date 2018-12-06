<?php

namespace AppBundle\Controller\Admin;

use Biz\File\Service\UploadFileService;
use Biz\System\Service\SettingService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;

class UploadFileController extends BaseController
{
    public function headLeaderParamsAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $params = $request->query->all();

        $params['user'] = $user->id;
        $params['targetType'] = 'headLeader';
        $params['targetId'] = '0';
        $params['convertor'] = 'HLSEncryptedVideo';
        $params['videoQuality'] = 'normal';
        $params['audioQuality'] = 'normal';
        $params['convertCallback'] = null;

        $params = $this->getUploadFileService()->makeUploadParams($params);

        return $this->createJsonResponse($params);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
