<?php

namespace Topxia\Api\Filter;
use Topxia\Service\Common\ServiceKernel;

class UserFilter implements Filter
{
	//输出前的字段控制
    //查看权限,附带内容可以写在这里
    public function filter(array &$data)
    {
        unset($data['password']);
        unset($data['salt']);
        unset($data['payPassword']);
        unset($data['payPasswordSalt']);

        $fileService = ServiceKernel::instance()->createService('Content.FileService');
        $userService = ServiceKernel::instance()->createService('User.UserService');
        $data['promotedTime'] = date('c', $data['promotedTime']);
        $data['lastPasswordFailTime'] = date('c', $data['lastPasswordFailTime']);
        $data['loginTime'] = date('c', $data['loginTime']);
        $data['approvalTime'] = date('c', $data['approvalTime']);
        $data['createdTime'] = date('c', $data['createdTime']);

        $smallAvatar = empty($data['smallAvatar']) ? '' : $fileService->parseFileUri($data['smallAvatar']);
        $data['smallAvatar'] = '/files'.$smallAvatar['fullpath'];

        $mediumAvatar = empty($data['mediumAvatar']) ? '' : $fileService->parseFileUri($data['mediumAvatar']);
        $data['mediumAvatar'] = '/files'.$mediumAvatar['fullpath'];

        $largeAvatar = empty($data['largeAvatar']) ? '' : $fileService->parseFileUri($data['largeAvatar']);
        $data['largeAvatar'] = '/files'.$largeAvatar['fullpath'];
        
        $user = getCurrentUser();
        $profile = $userService->getUserProfile($data['id']);
        if (!($user->isAdmin() || $user['id'] == $data['id'])) {
            unset($data['email']);
            unset($data['verifiedMobile']);
            unset($data['uri']);
            unset($data['tags']);
            unset($data['type']);
            unset($data['point']);
            unset($data['coin']);
            unset($data['emailVerified']);
            unset($data['setup']);
            unset($data['promoted']);
            unset($data['promotedTime']);
            unset($data['locked']);//TODO 是否需要处理
            unset($data['lockDeadline']);//TODO 是否需要处理
            unset($data['lastPasswordFailTime']);
            unset($data['consecutivePasswordErrorTimes']);
            unset($data['loginTime']);
            unset($data['loginIp']);
            unset($data['loginSessionId']);
            unset($data['approvalTime']);
            unset($data['approvalStatus']);
            unset($data['newMessageNum']);
            unset($data['newNotificationNum']);
            unset($data['createdIp']);
            unset($data['createdTime']);
            $data['about'] = $profile['about'];
            return $data;
        }
        return array_merge($data,$profile);

    }

    public function filters(array &$datas)
    {
        $num = 0;
        $results = array();
        foreach ($datas as $data) {
            $results[$num] = $this->filter($data);
            $num++;
        }
        return $results;
    }

}

