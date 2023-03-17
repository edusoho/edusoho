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
        $this->reconvertAction($globalId);
    }

    public function reconvertAction($globalId)
    {
        $this->getUploadFileService()->tryManageGlobalFile($globalId);

        $uploadFile = $this->getCloudFileService()->reconvert($globalId);

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
