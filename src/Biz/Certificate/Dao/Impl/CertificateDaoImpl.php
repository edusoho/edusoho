<?php

namespace Biz\Certificate\Dao\Impl;

use Biz\Certificate\Dao\CertificateDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CertificateDaoImpl extends GeneralDaoImpl implements CertificateDao
{
    protected $table = 'certificate';

    public function getByCode($code)
    {
        return $this->getByFields(['code' => $code]);
    }

    public function findByIds(array $ids = [])
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'updatedTime'],
            'conditions' => [
                'id = :id',
                'name like :nameLike',
                'targetType = :targetType',
                'status = :status',
                'targetId = :targetId',
            ],
        ];
    }
}
