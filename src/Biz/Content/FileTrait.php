<?php

namespace Biz\Content;

use AppBundle\Common\FileToolkit;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File as FileObject;

trait FileTrait
{
    private function fileDecode($str)
    {
        if (empty($str)) {
            return $str;
        }
        // data:{mimeType};base64,{code}
        $user = $this->getCurrentUser();
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $str, $result)) {
            $directory = $this->checkDirectory($this->biz['topxia.upload.private_directory'] . '/tmp');
            $filePath = $directory . '/' . $user['id'] . '_' . time() . '.' . $result[2];
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

    private function checkDirectory($directory)
    {
        $filesystem = new Filesystem();
        if (!$filesystem->exists($directory)) {
            $filesystem->mkdir($directory);
        }

        return $directory;
    }
}
