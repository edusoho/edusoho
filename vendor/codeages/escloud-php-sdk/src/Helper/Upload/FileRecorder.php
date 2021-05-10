<?php

namespace ESCloud\SDK\Helper\Upload;

class FileRecorder
{
    private $directory = 'tmp/';

    public function __construct($directory)
    {
        $this->directory = $directory;
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    public function get($key)
    {
        if (file_exists($this->directory . $key)) {
            return json_decode(file_get_contents($this->directory . $key));
        }

        return array();
    }

    public function set($key, $content)
    {
        return file_put_contents($this->directory . $key, json_encode($content));
    }

    public function recorderKeyGenerate($key, $filePath)
    {
        return hash_hmac('sha1', $key . filemtime($filePath) . $filePath, $key);
    }

    public function del($key)
    {
        return unlink($this->directory . $key);
    }
}
