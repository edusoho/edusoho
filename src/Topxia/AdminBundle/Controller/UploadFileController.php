<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

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

	protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }
}