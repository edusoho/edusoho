<?php

namespace ApiBundle\Api\Resource\UploadFileCategory;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\File\Service\UploadFileService;

class UploadFileCategory extends AbstractResource
{
    /**
     * @Access(permissions="admin_v2_cloud_resource")
     */
    public function add(ApiRequest $request)
    {
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
