<?php

namespace ApiBundle\Api\Resource\CertificateRecord;

use ApiBundle\Api\Resource\Certificate\CertificateFilter;
use ApiBundle\Api\Resource\Filter;

class CertificateRecordFilter extends Filter
{
    protected $publicFields = [
        'id',
        'userId',
        'truename',
        'certificateId',
        'certificateCode',
        'targetType',
        'targetId',
        'status',
        'rejectReason',
        'auditUserId',
        'auditTime',
        'expiryTime',
        'issueTime',
        'createdTime',
        'updatedTime',
        'certificate',
    ];

    protected function publicFields(&$data)
    {
        $data['auditTime'] = date('c', $data['auditTime']);
        $data['expiryTime'] = date('c', $data['expiryTime']);
        $data['issueTime'] = date('c', $data['issueTime']);
        if (!empty($data['certificate'])) {
            $certificateFilter = new CertificateFilter();
            $certificateFilter->setMode(Filter::SIMPLE_MODE);
            $certificateFilter->filter($data['certificate']);
        }
    }
}
