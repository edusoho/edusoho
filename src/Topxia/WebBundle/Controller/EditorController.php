<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Util\UploadToken;

class EditorController extends BaseController
{
    public function uploadAction(Request $request)
    {
        try {

            $token = $request->query->get('token');

            $maker = new UploadToken();
            $result = $maker->parse($token);
            if (empty($result)) {
                throw new \RuntimeException("上传授权码已过期，请刷新页面后重试！");
            }

            $file = $request->files->get('upload');

            $record = $this->getFileService()->uploadFile($result['group'], $file);

            $funcNum = $request->query->get('CKEditorFuncNum');
            $url = $this->get('topxia.twig.web_extension')->getFilePath($record['uri']);
            $response = "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$url}', function(){ this._.dialog.getParentEditor().insertHtml('<img src=\"{$url}\">'); this._.dialog.hide(); return false; });</script>";
            return new Response($response);


        } catch (\Exception $e) {
            $message = $e->getMessage();
            $funcNum = $request->query->get('CKEditorFuncNum');
            $response = "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '', '{$message}');</script>";
            return new Response($response);
        }
    }

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

}