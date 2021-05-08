<?php

namespace ESCloud\SDK\Helper\Upload;

use ESCloud\SDK\HttpClient\Client;

class ResumeUploader
{
    private $upToken;
    private $key;
    private $inputStream;
    private $size;
    private $mime;
    private $contexts;
    private $host = 'http://upload.qiqiuyun.net';
    const BLOCK_SIZE = 4194304;
    private $client;
    private $recorder = null;
    private $filepath;

    public function __construct($filePath, $upToken, $key, $mime, $recorder)
    {
        $file = fopen($filePath, 'rb');
        if ($file === false) {
            throw new \Exception("file can not open", 1);
        }
        $stat = fstat($file);
        $this->size = $stat['size'];

        $this->filepath = $filePath;
        $this->upToken = $upToken;
        $this->key = $key;
        $this->inputStream = $file;
        $this->mime = $mime;
        $this->contexts = array();
        $this->recorder = new FileRecorder($recorder);
    }

    /**
     * 上传操作
     */
    public function upload($filename)
    {
        $uploaded = $this->recoveryFromRecord();
        while ($uploaded < $this->size) {
            $blockSize = $this->blockSize($uploaded);
            $data = fread($this->inputStream, $blockSize);
            if ($data === false) {
                throw new \Exception("file read failed", 1);
            }
            $response = $this->makeBlock($data, $blockSize);
            $result = json_decode($response->getBody(), true);
            array_push($this->contexts, $result['ctx']);
            $uploaded += $blockSize;
            $this->setRecord($uploaded);
        }
        return $this->makeFile($filename);
    }

    /**
     * 创建块
     */
    private function makeBlock($block, $blockSize)
    {
        $url = $this->host . '/mkblk/' . $blockSize;
        return $this->post($url, $block);
    }

    private function fileUrl($fname)
    {
        $url = $this->host . '/mkfile/' . $this->size;
        $url .= '/mimeType/' . $this->base64_urlSafeEncode($this->mime);
        if ($this->key != null) {
            $url .= '/key/' . $this->base64_urlSafeEncode($this->key);
        }
        $url .= '/fname/' . $this->base64_urlSafeEncode($fname);
        if (!empty($this->params)) {
            foreach ($this->params as $key => $value) {
                $val = $this->base64_urlSafeEncode($value);
                $url .= "/$key/$val";
            }
        }
        return $url;
    }

    /**
     * 创建文件
     */
    private function makeFile($fname)
    {
        $url = $this->fileUrl($fname);
        $body = implode(',', $this->contexts);
        $response = $this->post($url, $body);

//        $this->recorder->del($this->recorder->recorderKeyGenerate($this->key, $this->filepath));
        return $response;
    }

    private function post($url, $data)
    {
        $options['headers'] = array('Authorization' => 'UpToken ' . $this->upToken);
        $options['body'] = $data;
        return $this->createClient()->request('POST', $url, $options);
    }

    private function blockSize($uploaded)
    {
        if ($this->size < $uploaded + self::BLOCK_SIZE) {
            return $this->size - $uploaded;
        }
        return self::BLOCK_SIZE;
    }

    function base64_urlSafeEncode($data)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($data));
    }

    protected function createClient()
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = new Client(array());

        return $this->client;
    }

    private function recoveryFromRecord()
    {
        $recorderKey = $this->recorder->recorderKeyGenerate($this->key, $this->filepath);

        $content = $this->recorder->get($recorderKey);

        if ($content) {
            for ($i = 0; $i < count($content['contexts']); $i++) {
                $this->contexts[$i] = $content['contexts'][$i];
            }
            return $content['offset'];
        }

        return 0;
    }

    private function setRecord($offset)
    {
        $recorderKey = $this->recorder->recorderKeyGenerate($this->key, $this->filepath);
        $this->recorder->set($recorderKey, array('size' => $this->size, 'offset' => $offset, 'modifyTime' => filemtime($this->filepath), 'contexts' => $this->contexts));
    }
}
