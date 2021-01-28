<?php

namespace Codeages\Biz\ItemBank\FaceInspection\Service\Impl;

use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerSceneException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\FaceInspection\Dao\RecordDao;
use Codeages\Biz\ItemBank\FaceInspection\Service\FaceInspectionService;
use Codeages\Biz\ItemBank\FaceInspection\Util\Behavior;
use Firebase\JWT\JWT;

class FaceInspectionServiceImpl extends BaseService implements FaceInspectionService
{
    public function createRecord($record)
    {
        $record = $this->getValidator()->validate($record, [
            'user_id' => ['required', ['min', 1]],
            'answer_scene_id' => ['required', ['min', 0]],
            'answer_record_id' => ['required', ['min', 0]],
            'status' => ['required'],
            'level' => ['required'],
            'duration' => ['required'],
            'behavior' => ['required'],
            'picture_path' => ['required'],
        ]);

        $answerScene = $this->getAnswerSceneService()->get($record['answer_scene_id']);
        if (empty($answerScene)) {
            throw new AnswerSceneException('AnswerScene not found.', ErrorCode::ANSWER_SCENE_NOTFOUD);
        }

        $answerRecord = $this->getAnswerRecordService()->get($record['answer_record_id']);
        if (empty($answerRecord)) {
            throw new AnswerException('Answer record not found.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }

        return $this->getRecordDao()->create($record);
    }

    public function countRecord($conditions)
    {
        return $this->getRecordDao()->count($conditions);
    }

    public function searchRecord($conditions, $orderBys, $start, $limit, $columns = array())
    {
        $results = $this->getRecordDao()->search($conditions, $orderBys, $start, $limit, $columns);

        if (empty($columns) || in_array('behavior', $columns)) {
            foreach ($results as &$result) {
                $result['msg'] = Behavior::getErrorMsg($result['behavior']);
            }
        }

        return $results;
    }

    public function makeToken($userId, $accessKey, $secretKey)
    {
        $payload = array(
            'aud' => 'inspection-service',
            'exp' => time() + 600,
            'subject_no' => 'capture_face',
            'user_no' => $userId,
        );

        return JWT::encode($payload, $secretKey, 'HS256', $accessKey);
    }

    /**
     * @return RecordDao
     */
    protected function getRecordDao()
    {
        return $this->biz->dao('ItemBank:FaceInspection:RecordDao');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerRecordService');
    }
}
