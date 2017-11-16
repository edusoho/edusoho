<?php

namespace AppBundle\Controller;

use AppBundle\Util\UploadToken;
use AppBundle\Common\CurlToolkit;
use AppBundle\Common\FileToolkit;
use Biz\Content\Service\FileService;
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
                throw $this->createAccessDeniedException('上传授权码已过期，请刷新页面后重试！');
            }

            $isWebuploader = $request->query->get('isWebuploader', 0);

            if ($isWebuploader) {
                $file = $request->files->get('file');
            } else {
                $file = $request->files->get('upload');
            }

            if ($token['type'] == 'image') {
                if (!FileToolkit::isImageFile($file)) {
                    throw $this->createAccessDeniedException('您上传的不是图片文件，请重新上传。');
                }
            } elseif ($token['type'] == 'flash') {
                $errors = FileToolkit::validateFileExtension($file, 'swf');

                if (!empty($errors)) {
                    throw $this->createAccessDeniedException('您上传的不是Flash文件，请重新上传。');
                }
            } else {
                throw $this->createAccessDeniedException('上传类型不正确！');
            }

            $record = $this->getFileService()->uploadFile($token['group'], $file);

            $parsed = $this->getFileService()->parseFileUri($record['uri']);
            FileToolkit::reduceImgQuality($parsed['fullpath'], 7);

            //$url    = $this->get('web.twig.extension')->getFilePath($record['uri']);
            $url = rtrim($this->container->getParameter('topxia.upload.public_url_path'), ' /').DIRECTORY_SEPARATOR.$parsed['path'];

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
                $funcNum = $request->query->get('CKEditorFuncNum');
                $response = "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '', '{$message}');</script>";

                return new Response($response);
            }
        }
    }

    public function downloadAction(Request $request)
    {
        $token = $request->query->get('token');
        $url = $request->request->get('url');
        $url = str_replace(' ', '%20', $url);
        $url = str_replace('+', '%2B', $url);
        $url = str_replace('#', '%23', $url);
        if(!preg_match('/^https?\:\/\/formula\.edusoho\.net/', $url)) {
            throw $this->createAccessDeniedException('上传授权域名不正确，请输入合法的域名！');
        }
        $maker = new UploadToken();
        $token = $maker->parse($token);

        if (empty($token)) {
            throw $this->createAccessDeniedException('上传授权码已过期，请刷新页面后重试！');
        }

        $name = date('Ymdhis').'_formula.jpg';
        $path = $this->get('service_container')->getParameter('topxia.upload.public_directory').'/tmp/'.$name;

        $imageData = CurlToolkit::request('POST', $url, array(), array('contentType' => 'plain'));

        $tp = @fopen($path, 'a');
        fwrite($tp, $imageData);
        fclose($tp);
        $record = $this->getFileService()->uploadFile($token['group'], new File($path));
        $url = $this->get('web.twig.extension')->getFilePath($record['uri']);

        return new Response($url);
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->getBiz()->service('Content:FileService');
    }
}
