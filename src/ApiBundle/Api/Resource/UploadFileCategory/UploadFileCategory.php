<?php

namespace ApiBundle\Api\Resource\UploadFileCategory;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\File\Service\UploadFileService;
use Biz\User\UserException;

class UploadFileCategory extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        if (!$this->getCurrentUser()->hasPermission('admin_v2_cloud_resource')) {
            throw UserException::PERMISSION_DENIED();
        }
        $data = $request->request->all();
        $this->getUploadFileService()->batchSetCategoryId($data['ids'], $data['categoryId']);

        return ['ok' => true];
    }

    /**
     * @return UploadFileService
     */
    private function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }
}
