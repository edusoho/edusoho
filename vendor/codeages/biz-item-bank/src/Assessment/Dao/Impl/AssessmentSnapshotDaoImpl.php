<?php

namespace Codeages\Biz\ItemBank\Assessment\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSnapshotDao;

class AssessmentSnapshotDaoImpl extends AdvancedDaoImpl implements AssessmentSnapshotDao
{
    protected $table = 'biz_assessment_snapshot';

    public function getBySnapshotAssessmentId($snapshotAssessmentId)
    {
        return $this->getByFields(['snapshot_assessment_id' => $snapshotAssessmentId]);
    }

    public function findBySnapshotAssessmentIds(array $snapshotAssessmentIds)
    {
        return $this->findInField('snapshot_assessment_id', $snapshotAssessmentIds);
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
            ],
            'serializes' => [
                'sections_snapshot' => 'json',
            ],
        ];
    }
}
