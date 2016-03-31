<?php

namespace MaterialLib\Service\MaterialLib\Impl;

use MaterialLib\Service\BaseService;

class PermissionServiceImpl extends BaseService
{
    public function check($permission, $options)
    {
        $method      = strtolower($permission);
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isLogin()) {
            return false;
        }

        if ($currentUser->isAdmin()) {
            return true;
        }

        return $this->$method($currentUser, $options);
    }

    public function view($currentUser, $options)
    {
        $file = $this->getUploadFileService()->getFileByGlobalId($options['globalId']);

        if ($file['isPublic']) {
            return true;
        }

        if ($file['createdUserId'] == $currentUser['id']) {
            return true;
        }
    }

    public function upload($currentUser, $options)
    {
        return $currentUser->isTeacher();
    }

    public function edit($currentUser, $options)
    {
        $file = $this->getUploadFileService()->getFileByGlobalId($options['globalId']);
        return $file['createdUserId'] == $currentUser['id'];
    }

    public function delete($currentUser, $options)
    {
        if ($currentUser->isAdmin()) {
            return true;
        }

        $file = $options['file'];
        return $file['createdUserId'] == $currentUser['id'];
    }

    public function search($currentUser, $options)
    {
        return $currentUser->isTeacher();
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService2');
    }
}
