<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FileController extends BaseController
{

    public function uploadAction (Request $request)
    {
        $group = $request->query->get('group');
        $file = $this->get('request')->files->get('file');

        $record = $this->getFileService()->uploadFile($group, $file);

        $record['url'] = $this->get('topxia.twig.web_extension')->getFilePath($record['uri']);

        return new Response(json_encode($record));

        return $this->createJsonResponse($record);
    }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

}