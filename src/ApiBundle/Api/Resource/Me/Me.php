<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;
use VipPlugin\Biz\Vip\Service\VipService;
use AppBundle\Common\FileToolkit;
use Symfony\Component\HttpFoundation\File\File;

class Me extends AbstractResource
{
    public function get(ApiRequest $request)
    {
        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($profile, $user);
        $this->appendUser($user);

        return $user;
    }

    public function update(ApiRequest $request)
    {
        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        $fields = $request->request->all();
        if (isset($fields['avatarId'])) {
            $this->updateAvatar($user, $fields['avatarId']);
        }
        $profile = $this->getUserService()->updateUserProfile($user['id'], $fields, false);
        $user = array_merge($profile, $user);
        $this->appendUser($user);

        return $user;
    }

    protected function updateAvatar($user, $fileId)
    {
        if (empty($fileId)) {
            return;
        }
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 270, 270);

        $options = $this->createImgCropOptions($naturalSize, $scaledSize);
        $record = $this->getFileService()->getFile($fileId);
        if (empty($record)) {
            throw new \RuntimeException('Error file not exists');
        }
        $parsed = $this->getFileService()->parseFileUri($record['uri']);

        $filePaths = FileToolKit::cropImages($parsed['fullpath'], $options);

        $fields = array();
        foreach ($filePaths as $key => $value) {
            $file = $this->getFileService()->uploadFile('user', new File($value));
            $fields[] = array(
                'type' => $key,
                'id' => $file['id'],
            );
        }

        if (isset($options['deleteOriginFile']) && 0 == $options['deleteOriginFile']) {
            $fields[] = array(
                'type' => 'origin',
                'id' => $record['id'],
            );
        } else {
            $this->getFileService()->deleteFileByUri($record['uri']);
        }

        if (empty($fields)) {
            throw new \RuntimeException('Error uplaod avatar');
        }
        $this->getUserService()->changeAvatar($user['id'], $fields);
    }

    private function createImgCropOptions($naturalSize, $scaledSize)
    {
        $options = array();

        $options['x'] = 0;
        $options['y'] = 0;
        $options['x2'] = $scaledSize->getWidth();
        $options['y2'] = $scaledSize->getHeight();
        $options['w'] = $naturalSize->getWidth();
        $options['h'] = $naturalSize->getHeight();

        $options['imgs'] = array();
        $options['imgs']['large'] = array(200, 200);
        $options['imgs']['medium'] = array(120, 120);
        $options['imgs']['small'] = array(48, 48);
        $options['width'] = $naturalSize->getWidth();
        $options['height'] = $naturalSize->getHeight();

        return $options;
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

    protected function getFileService()
    {
        return $this->service('Content:FileService');
    }
}
