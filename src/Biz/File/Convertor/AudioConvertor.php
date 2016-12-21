<?php

namespace Biz\File\Convertor;


class AudioConvertor extends BaseConvertor
{
    protected $name = 'audio';

    public function saveConvertResult($file, $result)
    {
    }

    public function getCovertParams($params)
    {
        return array(
            'convertor' => $this->name,
            'shd'       => 'mp3'
        );
    }
}