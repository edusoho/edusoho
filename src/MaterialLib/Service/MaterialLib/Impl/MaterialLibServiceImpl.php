<?php

namespace MaterialLib\Service\MaterialLib\Impl;

use MaterialLib\Service\MaterialLib\MaterialLibService;
use MaterialLib\Service\BaseService;

class MaterialLibServiceImpl extends BaseService implements MaterialLibService
{
    public function search($conditions, $start, $limit)
    {
        $conditions['start'] = $start;
        $conditions['limit'] = $limit;
        return $this->getUploadFileService()->search($conditions, 'cloud');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService2');
    }
}
