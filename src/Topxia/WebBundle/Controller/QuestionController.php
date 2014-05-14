<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Util\CloudClientFactory;

class QuestionController extends BaseController
{
    public function fileUrlAction(Request $request)
    {
        $id = $request->query->get('id');
        $file = $this->getUploadFileService()->getFile($id);
        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($file['targetType'] != 'question') {
            throw $this->createNotFoundException('targetType类型不正确');
        }

        if ($file['storage'] != 'cloud') {
            throw $this->createNotFoundException('storage类型不正确');
        }

        if ($file['convertStatus'] == 'waiting') {
            return $this->createJsonResponse(array('status' => 'waiting', 'message' => '音频正在转码中，请稍后再访问.'));
        }

        if ($file['convertStatus'] == 'error') {
            return $this->createJsonResponse(array('status' => 'waiting', 'message' => '音频转码失败，请重新上传此音频.'));
        }

        $factory = new CloudClientFactory();
        $client = $factory->createClient();
        $result = $client->generateFileUrl($client->getBucket(), $file['metas2']['shd']['key'], 3600);
        $result['status'] = 'ok';

        return $this->createJsonResponse($result);
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

}