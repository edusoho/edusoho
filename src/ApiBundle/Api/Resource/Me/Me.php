<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Util\AssetHelper;

class Me extends AbstractResource
{
    public function get(ApiRequest $request)
    {
        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        $this->appendUser($user);

        return $user;
    }

    public function update(ApiRequest $request)
    {
        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        $fields = $request->request->all();
        if (isset($fields['avatarId'])) {
            $user = $this->getUserService()->changeAvatarByFileId($user['id'], $fields['avatarId']);
        }
        $this->getUserService()->updateUserProfile($user['id'], $fields, false);
        $this->appendUser($user);

        return $user;
    }

    protected function appendUser(&$user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($profile, $user);

        if ($this->isPluginInstalled('vip')) {
            $vip = $this->service('VipPlugin:Vip:VipService')->getMemberByUserId($user['id']);
            $level = $this->service('VipPlugin:Vip:LevelService')->getLevel($vip['levelId']);
            if ($vip) {
                $user['vip'] = array(
                    'levelId' => $vip['levelId'],
                    'vipName' => $level['name'],
                    'deadline' => date('c', $vip['deadline']),
                    'seq' => $level['seq'],
                    'icon' => empty($level['icon']) ? AssetHelper::uriForPath('/assets/v2/img/vip/vip_icon_bronze.png') : AssetHelper::uriForPath($level['icon']),
                );
            } else {
                $user['vip'] = null;
            }
        }

        return $user;
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
