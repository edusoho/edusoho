<?php

namespace Biz\Certificate\Service\Impl;

use Biz\BaseService;
use Biz\Certificate\Dao\CertificateDao;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\TemplateService;

class CertificateServiceImpl extends BaseService implements CertificateService
{
    public function get($id)
    {
        return $this->getCertificateDao()->get($id);
    }

    public function search($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getCertificateDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function count($conditions)
    {
        return $this->getCertificateDao()->count($conditions);
    }

    /**
     * @return CertificateDao
     */
    protected function getCertificateDao()
    {
        return $this->createDao('Certificate:CertificateDao');
    }

    /**
     * @return TemplateService
     */
    protected function getTemplateService()
    {
        return $this->createService('Certificate:TemplateService');
    }
}
