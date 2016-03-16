<?php

namespace Topxia\Service\File\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\File\FileImplementor2;

class LocalFileImplementor2Impl extends BaseService implements FileImplementor2
{
    public function getFile($file)
    {

    }

    public function findFiles($file)
    {
    }

    public function prepareUpload($params)
    {
        return null;
    }

    public function finishedUpload($file, $params)
    {
        return array('success' => true, 'convertStatus' => 'success');
    }

    public function resumeUpload($hash, $params)
    {
    }

    public function getDownloadFile($id)
    {
    }

    public function deleteFile($file)
    {

    }

    public function search($conditions)
    {

    }
    
    public function initUpload($params)
    {
        $uploadParams = array();

        $uploadParams['uploadMode']          = 'local';
        $uploadParams['url']                 = "/uploadfile/upload?targetId={$params['targetId']}&targetType={$params['targetType']}";
        $uploadParams['postParams']          = array();
        $uploadParams['postParams']['token'] = $this->getUserService()->makeToken('fileupload', $params['userId'], strtotime('+ 2 hours'));

        return $uploadParams;
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
