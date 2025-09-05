<?php

namespace ApiBundle\Api\Resource\UploadFile;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\File\Service\UploadFileService;

class UploadFile extends AbstractResource
{
    /**
     * @Access(permissions="admin_v2_cloud_resource")
     */
    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = $this->buildSearchConditions($request->query->all());
        $files = $this->getUploadFileService()->searchFiles($conditions, ['createdTime' => 'DESC'], $offset, $limit);
        $total = $this->getUploadFileService()->searchFileCount($conditions);

        return $this->makePagingObject($files, $total, $offset, $limit);
    }

    private function buildSearchConditions($conditions)
    {
        $conditions['status'] = 'ok';
        $conditions['noTargetType'] = 'attachment';

        return $conditions;
    }

    /**
     * @return UploadFileService
     */
    private function getUploadFileService()
    {
        return $this->service('File:UploadFileService');
    }
}
