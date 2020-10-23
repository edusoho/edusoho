<?php

namespace Biz\CloudFile\Service\Impl;

use Biz\BaseService;
use Biz\CloudFile\Service\SupplierFileService;
use Biz\File\Service\FileImplementor;

class SupplierFileServiceImpl extends BaseService implements SupplierFileService
{
    public function player($globalId, $ssl = false)
    {
        return $this->getSupplierFileImplementor()->player($globalId, $ssl);
    }

    /**
     * @return FileImplementor
     */
    protected function getSupplierFileImplementor()
    {
        return $this->createService('File:SupplierFileImplementor');
    }
}
