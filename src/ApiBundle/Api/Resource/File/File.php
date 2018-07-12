<?php

namespace ApiBundle\Api\Resource\File;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\FileToolkit;
use Biz\Content\FileException;

class File extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $file = $request->getHttpRequest()->files->get('file', null);

        if (empty($file)) {
            throw FileException::FILE_NOT_UPLOAD();
        }
        
        $group = $request->request->get('group', null);
        if (!in_array($group, array('tmp', 'user', 'course'))) {
            throw FileException::FILE_GROUP_INVALID();
        }
        return $this->getFileService()->uploadFile($group, $file);
    }

    protected function getFileService()
    {
        return $this->service('Content:FileService');
    }

}
