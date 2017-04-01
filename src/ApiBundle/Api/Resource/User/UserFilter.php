<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\RequestUtil;
use AppBundle\Common\ArrayToolkit;

class UserFilter extends Filter
{
    protected $tokenFields = array(
        'id', 'email', 'locale', 'uri', 'nickname', 'title', 'type', 'smallAvatar', 'mediumAvatar', 'largeAvatar',
        'roles', 'promotedSeq', 'locked', 'currentIp', 'gender', 'iam', 'city', 'qq', 'signature', 'about', 'company',
        'job', 'school', 'class', 'weibo', 'weixin', 'isQQPublic', 'isWeixinPublic', 'isWeiboPublic', 'following',
        'follower', 'verifiedMobile', 'promotedTime', 'lastPasswordFailTime', 'loginTime', 'approvalTime'
    );

    protected $publicFields = array(
        'id', 'nickname', 'title', 'smallAvatar', 'mediumAvatar', 'largeAvatar'
    );

    protected function customFilter(&$data)
    {
        $filterMethod = $this->fieldMode.'Filter';
        $this->$filterMethod($data);
    }

    private function publicFilter(&$data)
    {
        $this->convertAvatar($data);
    }

    private function tokenFilter(&$data)
    {
        $this->convertAvatar($data);
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

    private function convertAvatar(&$data)
    {
        $data['smallAvatar'] = RequestUtil::asset($data['smallAvatar']);
        $data['mediumAvatar'] = RequestUtil::asset($data['mediumAvatar']);
        $data['largeAvatar'] = RequestUtil::asset($data['largeAvatar']);
    }

    private function convertAbsoluteUrl($html)
    {
        $html = preg_replace_callback('/src=[\'\"]\/(.*?)[\'\"]/', function($matches) {
            $absoluteUrl = RequestUtil::asset($matches[1]);
            return "src=\"{$absoluteUrl}\"";
        }, $html);

        return $html;

    }
}