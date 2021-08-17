<?php

namespace ApiBundle\Api\Resource\Teacher;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class TeacherFilter extends Filter
{
    protected $simpleFields = [
        'id', 'nickname', 'title', 'smallAvatar', 'mediumAvatar', 'largeAvatar', 'uuid', 'destroyed',
        'email', 'locale', 'uri', 'type', 'roles', 'promotedSeq', 'locked', 'currentIp', 'gender', 'iam', 'city', 'qq', 'signature', 'company',
        'job', 'school', 'class', 'weibo', 'weixin', 'isQQPublic', 'isWeixinPublic', 'isWeiboPublic', 'following', 'follower', 'verifiedMobile',
        'promotedTime', 'lastPasswordFailTime', 'loginTime', 'approvalTime', 'vip', 'token', 'havePayPassword', 'fingerPrintSetting', 'weChatQrCode',
        'loginIp', 'liveMultiClassNum', 'liveMultiClassStudentNum', 'endMultiClassNum', 'endMultiClassStudentNum',
    ];

    protected $mode = self::SIMPLE_MODE;

    protected function simpleFields(&$data)
    {
        $this->transformAvatar($data);
        $this->destroyedNicknameFilter($data);

        $data['promotedTime'] = date('c', $data['promotedTime']);
        $data['lastPasswordFailTime'] = date('c', $data['lastPasswordFailTime']);
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
        $data['weChatQrCode'] = !empty($data['weChatQrCode']) ? AssetHelper::getFurl($data['weChatQrCode'], 'avatar.png') : '';
        $data['avatar'] = [
            'small' => $data['smallAvatar'],
            'middle' => $data['mediumAvatar'],
            'large' => $data['largeAvatar'],
        ];

        unset($data['smallAvatar']);
        unset($data['mediumAvatar']);
        unset($data['largeAvatar']);
    }

    protected function destroyedNicknameFilter(&$data)
    {
        $data['nickname'] = (1 == $data['destroyed']) ? '帐号已注销' : $data['nickname'];
    }
}
