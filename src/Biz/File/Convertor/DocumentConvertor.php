<?php

namespace Biz\File\Convertor;

class DocumentConvertor extends BaseConvertor
{
    protected $name = 'document';

    public function saveConvertResult($file, $result)
    {
        $metas['thumb'] = $result['thumb'];
        $metas['pdf']   = $result['pdf'];
        $metas['swf']   = $result['swf'];

        $file['metas2']        = empty($file['metas2']) ? array() : $file['metas2'];
        $file['metas2']        = array_merge($file['metas2'], $metas);
        $file['convertStatus'] = 'success';

        return $file;
    }

    public function getCovertParams($params)
    {
        return array(
            'convertor' => $this->name
        );
    }
}