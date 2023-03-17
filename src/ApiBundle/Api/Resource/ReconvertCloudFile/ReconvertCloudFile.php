<?php

namespace ApiBundle\Api\Resource\ReconvertCloudFile;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\CloudFile\Service\CloudFileService;
use Biz\File\Service\UploadFileService;

class ReconvertCloudFile extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $globalId = $request->request->get('globalId');
        $file = $this->getUploadFileService()->getFileByGlobalId($globalId);
        if ('video' == $file['type']) {
            return $this->reconvert($file);
        }

        if (in_array($file['type'], ['video', 'audio'])) {
            return [
                'globalId' => $file['globalId'],
                'length' => $file['length'],
            ];
        }

        return (object) [];
    }

    public function reconvert($file)
    {
        $uploadFile = $this->getCloudFileService()->reconvert($file['globalId']);

        return $uploadFile;
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->biz->service('File:UploadFileService');
    }

    /**
     * @return CloudFileService
     */
    protected function getCloudFileService()
    {
        return $this->biz->service('CloudFile:CloudFileService');
    }
}
