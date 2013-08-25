<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class DiskController extends BaseController
{

    public function uploadAction(Request $request)
    {
        $file = $this->getDiskService()->addLocalFile(
            $this->get('request')->files->get('file'), '/'
        );

        var_dump($file);exit();

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