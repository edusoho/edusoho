<?php
namespace Topxia\AdminBundle\Controller;

use Biz\File\Service\UploadFileService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class UploadFileController extends BaseController
{
	public function headLeaderParamsAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $params = $request->query->all();

        $params['user'] = $user->id;
        $params['targetType'] = "headLeader";
        $params['targetId'] = "0";
        $params['convertor'] = "HLSEncryptedVideo";
        $params['videoQuality'] = "normal";
        $params['audioQuality'] = "normal";

        if (empty($params['lazyConvert'])) {
            $params['convertCallback'] = $this->generateUrl('uploadfile_cloud_convert_callback2', array(), true);
        } else {
            $params['convertCallback'] = null;
        }
        
        $params = $this->getUploadFileService()->makeUploadParams($params);

        return $this->createJsonResponse($params);
    }

    /**
     * @return SettingService
     */
	protected function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->getBiz()->service('File:UploadFileService');
    }
}