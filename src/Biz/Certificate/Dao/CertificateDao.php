<?php

namespace Biz\Certificate\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CertificateDao extends GeneralDaoInterface
{
    public function getByCode($code);
}
