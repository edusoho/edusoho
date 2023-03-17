<?php

namespace ApiBundle\Api\Resource\ReconvertCloudFile;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\CloudFile\Service\CloudFileService;
use Biz\File\Service\UploadFileService;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;

class ReconvertCloudFile extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $globalId = $request->query->get('globalId');
        $file =  $this->getAttachmentService()->getAttachmentByGlobalId($globalId);
//        $file = $this->getUploadFileService()->getFileByGlobalId($globalId);
        var_dump($file);
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

    /**
     * @return AttachmentService
     */
    protected function getAttachmentService()
    {
        return $this->biz->service('ItemBank:Item:AttachmentService');
    }
}
