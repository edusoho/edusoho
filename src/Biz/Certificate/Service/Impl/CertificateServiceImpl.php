<?php

namespace Biz\Certificate\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Certificate\CertificateException;
use Biz\Certificate\Dao\CertificateDao;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\TemplateService;
use Biz\Common\CommonException;
use Biz\System\Service\LogService;

class CertificateServiceImpl extends BaseService implements CertificateService
{
    public function get($id)
    {
        return $this->getCertificateDao()->get($id);
    }

    public function getCertificateByCode($code)
    {
        return $this->getCertificateDao()->getByCode($code);
    }

    public function search($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getCertificateDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function count($conditions)
    {
        return $this->getCertificateDao()->count($conditions);
    }

    public function create($fields)
    {
        if (!ArrayToolkit::requireds($fields, ['name', 'code'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $fields = $this->filterCertificateFields($fields);
        $fields['createdUserId'] = $this->getCurrentUser()->getId();

        $certificate = $this->getCertificateDao()->create($fields);

        $this->getLogService()->info(
            'certificate',
            'create',
            "证书创建, 证书 #{$certificate['id']} 名称 : 《{$certificate['name']}》"
        );

        return $certificate;
    }

    public function update($id, $fields)
    {
        $certificate = $this->get($id);
        if (empty($certificate)) {
            $this->createNewException(CertificateException::NOTFOUND_CERTIFICATE());
        }

        $fields = $this->filterCertificateFields($fields);

        return $this->getCertificateDao()->update($id, $fields);
    }

    public function publishCertificate($id)
    {
        $certificate = $this->get($id);
        if (empty($certificate)) {
            $this->createNewException(CertificateException::NOTFOUND_CERTIFICATE());
        }

        return $this->getCertificateDao()->update($id, ['status' => 'published']);
    }

    public function closeCertificate($id)
    {
        $certificate = $this->get($id);
        if (empty($certificate)) {
            $this->createNewException(CertificateException::NOTFOUND_CERTIFICATE());
        }

        return $this->getCertificateDao()->update($id, ['status' => 'closed']);
    }

    public function delete($id)
    {
        $certificate = $this->get($id);
        if (empty($certificate)) {
            $this->createNewException(CertificateException::NOTFOUND_CERTIFICATE());
        }

        if ('published' == $certificate['status']) {
            $this->createNewException(CertificateException::FORBIDDEN_DELETE_PUBLISHED());
        }

        return $this->getCertificateDao()->delete($id);
    }

    protected function filterCertificateFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            [
                'name',
                'templateId',
                'description',
                'code',
                'expiryDay',
                'autoIssue',
                'targetType',
                'targetId',
            ]
        );
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

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
