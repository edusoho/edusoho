<?php

namespace AppBundle\Controller;

use AppBundle\Common\Exception\FileToolkitException;
use AppBundle\Util\UploadToken;
use AppBundle\Common\CurlToolkit;
use AppBundle\Common\FileToolkit;
use Biz\Common\CommonException;
use Biz\Content\FileException;
use Biz\Content\Service\FileService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;

class EditorController extends BaseController
{
    public function uploadAction(Request $request)
    {
        $mode = $request->request->get('uploadMode', '');
        if ('paste' == $mode) {
            return $this->pasteImage($request);
        }
        $isWebuploader = 0;
        try {
            $token = $request->query->get('token');

            $maker = new UploadToken();
            $token = $maker->parse($token);

            if (empty($token)) {
                $this->createNewException(CommonException::EXPIRED_UPLOAD_TOKEN());
            }

            $isWebuploader = $request->query->get('isWebuploader', 0);

            if ($isWebuploader) {
                $file = $request->files->get('file');
            } else {
                $file = $request->files->get('upload');
            }

            if ('image' == $token['type']) {
                if (!FileToolkit::isImageFile($file)) {
                    $this->createNewException(FileToolkitException::NOT_IMAGE());
                }
            } elseif ('flash' == $token['type']) {
                $errors = FileToolkit::validateFileExtension($file, 'swf');

                if (!empty($errors)) {
                    $this->createNewException(FileToolkitException::NOT_FLASH());
                }
            } else {
                $this->createNewException(FileException::FILE_TYPE_ERROR());
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

                if ('image' == $token['type']) {
                    $response = "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$url}', function(){ this._.dialog.getParentEditor().insertHtml('<img src=\"{$url}\">'); this._.dialog.hide(); return false; });</script>";
                } else {
                    $response = "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$url}');</script>";
                }

                return new Response($response);
            }
        } catch (\Exception $e) {
            $message = $this->trans($e->getMessage());

            if ($isWebuploader) {
                return $this->createJsonResponse(array('message' => $message));
            } else {
                $funcNum = $request->query->get('CKEditorFuncNum');
                $response = "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '', '{$message}');</script>";

                return new Response($response);
            }
        }
    }

    protected function pasteImage(Request $request)
    {
        try {
            $token = $request->query->get('token');

            $maker = new UploadToken();
            $token = $maker->parse($token);

            if (empty($token)) {
                $this->createNewException(CommonException::EXPIRED_UPLOAD_TOKEN());
            }

            $file = $request->files->get('upload');

            if ('image' == $token['type']) {
                if (!FileToolkit::isImageFile($file)) {
                    $this->createNewException(FileToolkitException::NOT_IMAGE());
                }
            } else {
                $this->createNewException(FileException::FILE_TYPE_ERROR());
            }

            $record = $this->getFileService()->uploadFile($token['group'], $file);

            $parsed = $this->getFileService()->parseFileUri($record['uri']);
            FileToolkit::reduceImgQuality($parsed['fullpath'], 7);

            $url = rtrim($this->container->getParameter('topxia.upload.public_url_path'), ' /').DIRECTORY_SEPARATOR.$parsed['path'];

            return $this->createJsonResponse(array(
                'uploaded' => 1,
                'url' => $url,
                'fileName' => '',
            ));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array(
                'uploaded' => 0,
                'error' => array(
                    'message' => $e->getMessage(),
                ),
            ));
        }
    }

    public function downloadAction(Request $request)
    {
        $token = $request->query->get('token');
        $url = $request->request->get('url');
        $url = str_replace(' ', '%20', $url);
        $url = str_replace('+', '%2B', $url);
        $url = str_replace('#', '%23', $url);
        if (!preg_match('/^https?\:\/\/formula\.edusoho\.net/', $url)) {
            $this->createNewException(FileException::FILE_AUTH_URL_INVALID());
        }
        $maker = new UploadToken();
        $token = $maker->parse($token);

        if (empty($token)) {
            $this->createNewException(CommonException::EXPIRED_UPLOAD_TOKEN());
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
