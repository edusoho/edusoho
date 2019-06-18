<?php

namespace ApiBundle\Api\Resource\Setting;

use ApiBundle\Api\Resource\Filter;

class WeChatSettingFilter extends Filter
{
    protected $publicFields = array(
        'enabled', 'official_qrcode',
    );

    protected function publicFields(&$data)
    {
        $index = strpos($data['official_qrcode'], '//');
        $data['official_qrcode'] = (false !== $index && 0 === $index) ? $data['official_qrcode'] : $this->convertFilePath($data['official_qrcode']);
    }
}
