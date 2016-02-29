<?php

namespace MaterialLib\Service\MaterialLib\Impl;

use MaterialLib\Service\MaterialLib\MaterialLibService;

class MaterialLibServiceImpl extends MaterialLibService
{
    public function search($conditions, $start, $limit)
    {
        
    }

    public function searchCount($conditions)
    {
        
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService2');
    }
}
