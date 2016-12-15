<?php

namespace Biz\File\Convertor;

class HLSEncryptedVideoConvertor extends HLSVideoConvertor
{
    public function getCovertParams($params)
    {
        $params              = parent::getCovertParams($params);
        $params['convertor'] = 'HLSEncryptedVideo';
        $params['hlsKeyUrl'] = 'http://hlskey.edusoho.net/placeholder';
        $params['hlsKey']    = $this->generateKey();

        return $params;
    }

    public function saveConvertResult($file, $result)
    {
        $file = parent::saveConvertResult($file, $result);

        $moves = array(
            array("public:{$file['hashId']}", "private:{$file['hashId']}")
        );
        $result = $this->client->moveFiles($moves);

        $file['metas2']['protectSourceFile'] = empty($result['success_count']) ? 0 : $result['success_count'];

        return $file;
    }

    protected function generateKey()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $key = '';

        for ($i = 0; $i < 16; $i++) {
            $key .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $key;
    }
}
