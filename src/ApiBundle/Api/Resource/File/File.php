<?php

namespace ApiBundle\Api\Resource\File;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Content\FileException;
use Biz\Content\FileTrait;

class File extends AbstractResource
{
    use FileTrait;

    public function add(ApiRequest $request)
    {
        $group = $request->request->get('group', null);
        if (!in_array($group, array('default', 'tmp', 'user', 'course', 'system'))) {
            throw FileException::FILE_GROUP_INVALID();
        }

        $file = $request->request->get('file', null);
        $file = $this->fileDecode($file);
        if (empty($file)) {
            $file = $request->getHttpRequest()->files->get('file', null);
        }

        if (empty($file)) {
            throw FileException::FILE_NOT_UPLOAD();
        }

        return $this->getFileService()->uploadFile($group, $file);
    }

    protected function getFileService()
    {
        return $this->service('Content:FileService');
    }
}
