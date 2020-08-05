<?php

namespace Biz\Certificate\Dao\Impl;

use Biz\Certificate\Dao\TemplateDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TemplateDaoImpl extends GeneralDaoImpl implements TemplateDao
{
    protected $table = 'certificate_template';

    public function declares()
    {
        return array(
            'orderbys' => array('id', 'createdTime', 'updatedTime'),
            'conditions' => array(
                'id = :id',
                'name like :nameLike',
                'targetType = :targetType',
            ),
        );
    }
}