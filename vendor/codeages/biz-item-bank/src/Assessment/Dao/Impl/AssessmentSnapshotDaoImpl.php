<?php

namespace Codeages\Biz\ItemBank\Assessment\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSnapshotDao;

class AssessmentSnapshotDaoImpl extends AdvancedDaoImpl implements AssessmentSnapshotDao
{
    protected $table = 'biz_assessment_snapshot';

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
            ],
        ];
    }
}
