<?php

namespace Codeages\Biz\ItemBank\Assessment\Dao\Impl;

use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class AssessmentDaoImpl extends AdvancedDaoImpl implements AssessmentDao
{
    protected $table = 'biz_assessment';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        return array(
            'orderbys' => [
                'created_time',
            ],
            'timestamps' => [
                'created_time',
                'updated_time',
            ],
            'conditions' => [
                'id = :id',
                'bank_id = :bank_id',
                'name like :nameLike',
                'status = :status',
                'displayable = :displayable',
            ],
        );
    }
}
