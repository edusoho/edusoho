<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\RequestUtil;

class UserFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'nickname', 'title', 'smallAvatar', 'mediumAvatar', 'largeAvatar'
    );

    protected $publicFields = array(
    );

    protected $authenticatedFields = array(
        'email', 'locale', 'uri', 'type', 'roles', 'promotedSeq', 'locked', 'currentIp', 'gender', 'iam', 'city', 'qq', 'signature', 'about', 'company',
        'job', 'school', 'class', 'weibo', 'weixin', 'isQQPublic', 'isWeixinPublic', 'isWeiboPublic', 'following', 'follower', 'verifiedMobile', 'promotedTime', 'lastPasswordFailTime', 'loginTime', 'approvalTime', 'vip'
    );

    protected function simpleFields(&$data)
    {
        $data['smallAvatar'] = RequestUtil::asset($data['smallAvatar']);
        $data['mediumAvatar'] = RequestUtil::asset($data['mediumAvatar']);
        $data['largeAvatar'] = RequestUtil::asset($data['largeAvatar']);
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
}