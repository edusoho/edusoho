<?php

namespace Codeages\Biz\ItemBank\Assessment\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface AssessmentSnapshotDao extends AdvancedDaoInterface
{
    public function getBySnapshotAssessmentId($snapshotAssessmentId);

    public function findBySnapshotAssessmentIds(array $snapshotAssessmentIds);
}
