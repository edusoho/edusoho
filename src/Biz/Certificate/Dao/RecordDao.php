<?php

namespace Biz\Certificate\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface RecordDao extends AdvancedDaoInterface
{
    public function findByCertificateId($certificateId);

    public function findExpiredRecords($certificateId);

    public function findByUserIdsAndCertificateId($userIds, $certificateId);
}
