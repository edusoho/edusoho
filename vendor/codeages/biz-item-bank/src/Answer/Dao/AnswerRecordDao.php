<?php

namespace Codeages\Biz\ItemBank\Answer\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface AnswerRecordDao extends AdvancedDaoInterface
{
    public function getLatestAnswerRecordByAnswerSceneIdAndUserId($answerSceneId, $userId);

    public function getNextReviewingAnswerRecordByAnswerSceneId($answerSceneId);

    public function findByAnswerSceneId($answerSceneId);

    public function countGroupByAnswerSceneId($conditions);

    public function deleteByAssessmentId($assessmentId);

    public function findByIds($ids);
}
