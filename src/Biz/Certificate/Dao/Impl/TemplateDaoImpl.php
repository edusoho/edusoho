<?php

namespace Biz\Certificate\Dao\Impl;

use Biz\Certificate\Dao\TemplateDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TemplateDaoImpl extends GeneralDaoImpl implements TemplateDao
{
    protected $table = 'certificate_template';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'updatedTime'],
            'conditions' => [
                'id = :id',
                'name like :nameLike',
                'targetType = :targetType',
                'dropped = :dropped',
            ],
        ];
    }
}
