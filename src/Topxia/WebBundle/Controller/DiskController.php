<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class DiskController extends BaseController
{

    public function uploadAction(Request $request)
    {
        $token = $request->request->get('token');
        $token = $this->getUserService()->getToken('diskLocalUpload', $token);
        if (empty($token)) {
            throw $this->createAccessDeniedException('上传TOKEN已过期或不存在。');
        }

        $file = $this->get('request')->files->get('file');

        $file = $this->getDiskService()->addLocalFile($file, $token['userId'], '/');

        return $this->createJsonResponse($file);
    }

    public function uploadCallbackAction (Request $request)
    {
        $data = $request->request->all();
        $data['storage'] = 'cloud';

        $file = $this->getDiskService()->addCloudFile($data);

        return $this->createJsonResponse($file);
    }

    public function browseAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $type = $request->query->get('type');

        $files = $this->getDiskService()->searchFiles(array(
            'userId' => $user['id'],
            'type' => $type,
        ), 'lastestUpdated', 0, 1000);

        return $this->createFilesJsonResponse($files);
    }

    private function createFilesJsonResponse($files)
    {
        foreach ($files as &$file) {
            $file['updatedTime'] = date('Y-m-d H:i', $file['updatedTime']);
            $file['size'] = $this->formatFileSize($file['size']);
            unset($file);
        }
        return $this->createJsonResponse($files);
    }

    private function formatFileSize($size)
    {
        $currentValue = $currentUnit = null;
        $unitExps = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3);
        foreach ($unitExps as $unit => $exp) {
            $divisor = pow(1000, $exp);
            $currentUnit = $unit;
            $currentValue = $size / $divisor;
            if ($currentValue < 1000) {
                break;
            }
        }

        return sprintf('%.1f', $currentValue) . $currentUnit;
    }

    private function getDiskService()
    {
        return $this->getServiceKernel()->createService('User.DiskService');
    }

}