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

    public function findTypes()
    {
        $sql = "SELECT `type` FROM `{$this->table}` group by `type`";

        return $this->db()->fetchAll($sql);
    }

    public function declares()
    {
        return array(
            'orderbys' => [
                'id',
                'created_time',
            ],
            'timestamps' => [
                'created_time',
                'updated_time',
            ],
            'conditions' => [
                'id = :id',
                'id IN (:ids)',
                'id NOT IN (:notInIds)',
                'bank_id = :bank_id',
                'parent_id = :parent_id',
                'name like :nameLike',
                'status = :status',
                'displayable = :displayable',
                'type = :type',
                'parent_id = : parent_id',
                'created_user_id in (:created_user_ids)'
            ],
        );
    }
}
