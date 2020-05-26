<?php
namespace Codeages\Biz\ItemBank\Answer\Dao;

interface AnswerRecordDao
{
    public function getLatestAnswerRecordByAnswerSceneIdAndUserId($answerSceneId, $userId);

    public function getNextReviewingAnswerRecordByAnswerSceneId($answerSceneId);

    public function findByAnswerSceneId($answerSceneId);
}
