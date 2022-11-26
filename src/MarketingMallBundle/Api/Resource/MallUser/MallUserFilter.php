<?php

namespace MarketingMallBundle\Api\Resource\MallUser;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class MallUserFilter extends Filter
{
    protected $simpleFields = ['id', 'nickname', 'email', 'verifiedMobile', 'password', 'smallAvatar', 'title', 'about'];

    protected function simpleFields(&$data)
    {
        $data['smallAvatar'] = AssetHelper::getFurl($data['smallAvatar'], 'avatar.png');
        $data['about'] = $this->convertAbsoluteUrl($data['about']);
    }
}
