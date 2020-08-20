<?php

namespace ApiBundle\Api\Resource\CertificateRecord;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Certificate\CertificateException;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\RecordService;
use Biz\User\Service\UserService;

class CertificateRecord extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $id)
    {
        $record = $this->getCertificateRecordService()->get($id);
        if (empty($record)) {
            throw CertificateException::NOTFOUND_RECORD();
        }
        $record['certificate'] = $this->getCertificateService()->get($record['certificateId']);
        $user = $this->getUserService()->getUserAndProfile($record['userId']);
        $record['truename'] = empty($user['truename']) ? '' : $user['truename'];

        return $record;
    }

    /**
     * @return UserService
     */
    public function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return CertificateService
     */
    public function getCertificateService()
    {
        return $this->getBiz()->service('Certificate:CertificateService');
    }

    /**
     * @return RecordService
     */
    public function getCertificateRecordService()
    {
        return $this->getBiz()->service('Certificate:RecordService');
    }
}
