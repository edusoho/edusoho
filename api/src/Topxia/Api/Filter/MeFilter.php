<?php

namespace Topxia\Api\Filter;
use Topxia\Service\Common\ServiceKernel;
class MeFilter implements Filter
{
	//输出前的字段控制
    //查看权限,附带内容可以写在这里
    public function filter(array &$data)
    {
        if (empty($data['id'])) {
            return $data;
        }
        $fileService = ServiceKernel::instance()->createService('Content.FileService');
        $userService = ServiceKernel::instance()->createService('User.UserService');
        unset($data['password']);
        unset($data['salt']);
        unset($data['payPassword']);
        unset($data['payPasswordSalt']);
        $data['promotedTime'] = date('c', $data['promotedTime']);
        $data['lastPasswordFailTime'] = date('c', $data['lastPasswordFailTime']);
        $data['loginTime'] = date('c', $data['loginTime']);
        $data['approvalTime'] = date('c', $data['approvalTime']);
        $data['createdTime'] = date('c', $data['createdTime']);

        $smallAvatar = empty($data['smallAvatar']) ? '' : $fileService->parseFileUri($data['smallAvatar']);
        $data['smallAvatar'] = empty($smallAvatar) ? '' : 'files/'.$smallAvatar['path'];

        $mediumAvatar = empty($data['mediumAvatar']) ? '' : $fileService->parseFileUri($data['mediumAvatar']);
        $data['mediumAvatar'] = empty($mediumAvatar) ? '' : 'files/'.$mediumAvatar['path'];

        $largeAvatar = empty($data['largeAvatar']) ? '' : $fileService->parseFileUri($data['largeAvatar']);
        $data['largeAvatar'] = empty($largeAvatar) ? '' : 'files/'.$largeAvatar['path'];

        $profile = $userService->getUserProfile($data['id']);
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

