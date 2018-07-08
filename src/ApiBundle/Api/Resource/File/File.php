<?php

namespace ApiBundle\Api\Resource\File;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\FileToolkit;

class File extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $file = $request->getHttpRequest()->files->get('file', null);
        $group = $request->request->get('group', null);
        if (!in_array($group, array('tmp', 'user', 'course'))) {
            //抛异常
        }
        return $this->getFileService()->uploadFile($group, $file);
    }

    protected function getFileService()
    {
        return $this->service('Content:FileService');
    }

}
