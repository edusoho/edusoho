<?php

namespace Topxia\Service\File\Convertor;

class AudioConvertor
{
    const NAME = 'audio';

    protected $client;

    protected $config = array();

    public function __construct($client, $config)
    {
    }

    public function saveConvertResult($file, $result)
    {
    }

    public function getCovertParams($params)
    {
        return array(
            'convertor' => self::NAME,
            'shd'       => 'mp3'
        );
    }
}