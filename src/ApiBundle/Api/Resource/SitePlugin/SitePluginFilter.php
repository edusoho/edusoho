<?php

namespace ApiBundle\Api\Resource\SitePlugin;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\Converter;

class SitePluginFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'name', 'code',
    );
    protected $publicFields = array(
        'plugin', 'description', 'version', 'installedTime',
    );

    protected function publicFields(&$data)
    {
        Converter::timestampToDate($data['installedTime']);
    }
}
