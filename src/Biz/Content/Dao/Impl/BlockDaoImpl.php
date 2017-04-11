<?php

namespace Biz\Content\Dao\Impl;

use Biz\Content\Dao\BlockDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class BlockDaoImpl extends GeneralDaoImpl implements BlockDao
{
    protected $table = 'block';

    public function getByTemplateIdAndOrgId($blockTemplateId, $orgId = 0)
    {
        return $this->getByFields(array(
            'blockTemplateId' => $blockTemplateId,
            'orgId' => $orgId,
        ));
    }

    public function getByTemplateId($blockTemplateId)
    {
        return $this->getByFields(array(
            'blockTemplateId' => $blockTemplateId,
        ));
    }

    public function getByCode($code)
    {
        return $this->getByFields(array(
            'code' => $code,
        ));
    }

    public function getByCodeAndOrgId($code, $orgId = 0)
    {
        return $this->getByFields(array(
            'code' => $code,
            'orgId' => $orgId,
        ));
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'meta' => 'json',
                'data' => 'json',
            ),
        );
    }
}
