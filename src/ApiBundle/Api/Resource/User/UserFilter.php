<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class UserFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'nickname', 'title', 'smallAvatar', 'mediumAvatar', 'largeAvatar', 'about',
    );

    protected $publicFields = array(
    );

    protected $authenticatedFields = array(
        'email', 'locale', 'uri', 'type', 'roles', 'promotedSeq', 'locked', 'currentIp', 'gender', 'iam', 'city', 'qq', 'signature', 'about', 'company',
        'job', 'school', 'class', 'weibo', 'weixin', 'isQQPublic', 'isWeixinPublic', 'isWeiboPublic', 'following', 'follower', 'verifiedMobile', 'promotedTime', 'lastPasswordFailTime', 'loginTime', 'approvalTime', 'vip',
    );

    protected $mode = self::SIMPLE_MODE;

    protected function simpleFields(&$data)
    {
        $this->transformAvatar($data);
    }

    protected function authenticatedFields(&$data)
    {
        $data['about'] = $this->convertAbsoluteUrl($data['about']);

        $data['promotedTime'] = date('c', $data['promotedTime']);
        $data['lastPasswordFailTime'] = date('c', $data['lastPasswordFailTime']);
        $data['loginTime'] = date('c', $data['loginTime']);
        $data['approvalTime'] = date('c', $data['approvalTime']);
        $data['email'] = '*****';
        if (!empty($data['verifiedMobile'])) {
            $data['verifiedMobile'] = substr_replace($data['verifiedMobile'], '****', 3, 4);
        } else {
            unset($data['verifiedMobile']);
        }
    }

    private function transformAvatar(&$data)
    {
        $data['smallAvatar'] = AssetHelper::getFurl($data['smallAvatar'], 'avatar.png');
        $data['mediumAvatar'] = AssetHelper::getFurl($data['mediumAvatar'], 'avatar.png');
        $data['largeAvatar'] = AssetHelper::getFurl($data['largeAvatar'], 'avatar.png');
        $data['avatar'] = array(
            'small' => $data['smallAvatar'],
            'middle' => $data['mediumAvatar'],
            'large' => $data['largeAvatar'],
        );

        unset($data['smallAvatar']);
        unset($data['mediumAvatar']);
        unset($data['largeAvatar']);
    }
}
