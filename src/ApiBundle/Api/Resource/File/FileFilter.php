<?php

namespace ApiBundle\Api\Resource\File;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class FileFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'uri', 'size', 'createdTime',
    );

    protected function simpleFields(&$data)
    {
        $data['uri'] = AssetHelper::getFurl($data['uri']);
    }
}
