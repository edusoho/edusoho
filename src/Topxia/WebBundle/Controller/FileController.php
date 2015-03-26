<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Util\UploadToken;
use Topxia\Common\FileToolkit;

class FileController extends BaseController
{

    public function uploadAction(Request $request)
    {
        $token = $request->request->get('token');

        $maker = new UploadToken();
        $token = $maker->parse($token);

        if (empty($token)) {
            throw new \RuntimeException("上传授权码已过期，请刷新页面后重试！");
        }

        $file = $request->files->get('file');
        if ($token['type'] == 'image') {
            if (!FileToolkit::isImageFile($file)) {
                throw new \RuntimeException("您上传的不是图片文件，请重新上传。");
            }
        } else {
            throw new \RuntimeException("上传类型不正确！");
        }

        $groupCode = $token['group'];
        if(empty($groupCode)){
            $groupCode = "default";
        }
        
        $record = $this->getFileService()->uploadFile($groupCode, $file);
        $record['url'] = $this->get('topxia.twig.web_extension')->getFilePath($record['uri']);

        return $this->createJsonResponse($record);
    }

    public function cropImgAction(Request $request, $group)
    {
        $options = $request->request->all();

        $record = $this->getFileService()->getFile($options["fileId"]);
        $parsed = $this->getFileService()->parseFileUri($record['uri']);


        $filePaths = FileToolKit::cropImages($parsed["fullpath"], $options, $options["imgs"]);

        $fields = array();
        foreach ($filePaths as $key => $value) {
            $file = $this->getFileService()->uploadFile($group, new File($value));
            $fields[] = array(
                "type" => $key,
                "id" => $file['id']
            );
        }

        $this->getFileService()->deleteFileByUri($record["uri"]);

        return $this->createJsonResponse($fields);
    }

    // public function uploadAction (Request $request)
    // {
    //     $group = $request->query->get('group');
    //     $file = $this->get('request')->files->get('file');

    //     $record = $this->getFileService()->uploadFile($group, $file);

    //     $record['url'] = $this->get('topxia.twig.web_extension')->getFilePath($record['uri']);

    //     return new Response(json_encode($record));

    //     return $this->createJsonResponse($record);
    // }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

}