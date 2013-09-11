<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class FileController extends BaseController
{

    public function uploadAction (Request $request)
    {
        $group = $request->query->get('group');
        $file = $this->get('request')->files->get('file');

        $record = $this->getFileService()->uploadFile($group, $file);

        $record['url'] = $this->get('topxia.twig.web_extension')->getFilePath($record['uri']);

        return $this->createJsonResponse($record);
    }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

}