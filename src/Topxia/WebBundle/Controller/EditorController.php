<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\CurlToolkit;
use Topxia\Common\FileToolkit;
use Topxia\WebBundle\Util\UploadToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;

class EditorController extends BaseController
{
    public function uploadAction(Request $request)
    {
        $isWebuploader = 0;
        try {
            $token = $request->query->get('token');

            $maker = new UploadToken();
            $token = $maker->parse($token);

            if (empty($token)) {
                throw new \RuntimeException("上传授权码已过期，请刷新页面后重试！");
            }

            $isWebuploader = $request->query->get('isWebuploader', 0);

            if ($isWebuploader) {
                $file = $request->files->get('file');
            } else {
                $file = $request->files->get('upload');
            }

            if ($token['type'] == 'image') {
                if (!FileToolkit::isImageFile($file)) {
                    throw new \RuntimeException("您上传的不是图片文件，请重新上传。");
                }
            } elseif ($token['type'] == 'flash') {
                $errors = FileToolkit::validateFileExtension($file, 'swf');

                if (!empty($errors)) {
                    throw new \RuntimeException("您上传的不是Flash文件，请重新上传。");
                }
            } else {
                throw new \RuntimeException("上传类型不正确！");
            }

            $record = $this->getFileService()->uploadFile($token['group'], $file);

            $parsed = $this->getFileService()->parseFileUri($record['uri']);
            FileToolkit::reduceImgQuality($parsed['fullpath'], 7);

            $url    = $this->get('topxia.twig.web_extension')->getFilePath($record['uri']);

            if ($isWebuploader) {
                return $this->createJsonResponse(array('url' => $url));
            } else {
                $funcNum = $request->query->get('CKEditorFuncNum');

                if ($token['type'] == 'image') {
                    $response = "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$url}', function(){ this._.dialog.getParentEditor().insertHtml('<img src=\"{$url}\">'); this._.dialog.hide(); return false; });</script>";
                } else {
                    $response = "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$url}');</script>";
                }

                return new Response($response);
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();

            if ($isWebuploader) {
                return $this->createJsonResponse(array('message' => $message));
            } else {
                $funcNum  = $request->query->get('CKEditorFuncNum');
                $response = "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '', '{$message}');</script>";
                return new Response($response);
            }
        }
    }

    public function downloadAction(Request $request)
    {
        $token = $request->query->get('token');
        $url   = $request->request->get('url');
        $url   = str_replace(' ', '%20', $url);
        $url   = str_replace('+', '%2B', $url);
        $url   = str_replace('#', '%23', $url);
        $maker = new UploadToken();
        $token = $maker->parse($token);

        if (empty($token)) {
            throw new \RuntimeException("上传授权码已过期，请刷新页面后重试！");
        }

        $name = date("Ymdhis")."_formula.jpg";
        $path = $this->getServiceKernel()->getParameter('topxia.upload.public_directory').'/tmp/'.$name;

        $imageData = CurlToolkit::request('POST', $url, array(), array('contentType' => 'plain'));

        $tp = @fopen($path, 'a');
        fwrite($tp, $imageData);
        fclose($tp);
        $record = $this->getFileService()->uploadFile($token['group'], new File($path));
        $url    = $this->get('topxia.twig.web_extension')->getFilePath($record['uri']);
        return new Response($url);
    }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }
}
