<?php

namespace ApiBundle\Api\Resource\File;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\FileToolkit;
use Biz\Content\FileException;
use Symfony\Component\HttpFoundation\File\File as FileObject;
use Symfony\Component\Filesystem\Filesystem;

class File extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $group = $request->request->get('group', null);
        if (!in_array($group, array('default', 'tmp', 'user', 'course', 'system'))) {
            throw FileException::FILE_GROUP_INVALID();
        }

        $file = $request->request->get('file', null);
        $file = $this->fileDecode($file);
        if (empty($file)) {
            $file = $request->getHttpRequest()->files->get('file', null);
        }

        if (empty($file)) {
            throw FileException::FILE_NOT_UPLOAD();
        }

        return $this->getFileService()->uploadFile($group, $file);
    }

    protected function fileDecode($str)
    {
        if (empty($str)) {
            return $str;
        }
        // data:{mimeType};base64,{code}
        $user = $this->getCurrentUser();
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $str, $result)) {
            $directory = $this->checkDirectory($this->biz['topxia.upload.private_directory'].'/tmp');
            $filePath = $directory . '/' . $user['id'].'_'.time().'.'.$result[2];
            file_put_contents($filePath, base64_decode(str_replace($result[1], '', $str)));

            $file = new FileObject($filePath);

            $errors = FileToolkit::validateFileExtension($file);
            if ($errors) {
                @unlink($file->getRealPath());
                throw FileException::FILE_UPLOAD_NOT_ALLOWED();
            }

            return $file;
        }

        return null;
    }

    protected function checkDirectory($directory)
    {
        $filesystem = new Filesystem();
        if (!$filesystem->exists($directory)) {
            $filesystem->mkdir($directory);
        }

        return $directory;
    }

    protected function getFileService()
    {
        return $this->service('Content:FileService');
    }
}
