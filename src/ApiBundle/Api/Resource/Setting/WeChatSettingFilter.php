<?php

namespace ApiBundle\Api\Resource\Setting;

use ApiBundle\Api\Resource\Filter;

class WeChatSettingFilter extends Filter
{
    protected $publicFields = array(
        'enabled', 'official_name', 'official_qrcode',
    );

    protected function publicFields(&$data)
    {
        $data['official_qrcode'] = $this->convertFilePath($data['official_qrcode']);
    }
}
