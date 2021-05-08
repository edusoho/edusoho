<?php

namespace ESCloud\SDK\Helper\Upload;

class UploadManager
{
    public function upload($filePath, $key, $token, $mime = 'application/octet-stream', $recorder = 'tmp/')
    {
        $file = fopen($filePath, 'rb');
        if ($file === false) {
            throw new \Exception("file can not open", 1);
        }

        $resumeUploader = new ResumeUploader(
            $filePath,
            $token,
            $key,
            $mime,
            $recorder
        );
        $ret = $resumeUploader->upload(basename($filePath));
        fclose($file);
        return $ret;
    }
}
