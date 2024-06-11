<?php

namespace Biz\AI\Dao\Impl;

use Biz\AI\Dao\AIAnswerRecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AIAnswerRecordDaoImpl extends GeneralDaoImpl implements AIAnswerRecordDao
{
    protected $table = 'ai_answer_record';

    public function findByUserIdAndAppAndInputsHash($userId, $app, $inputsHash)
    {
        return $this->findByFields(['userId' => $userId, 'app' => $app, 'inputsHash' => $inputsHash]);
    }

    public function declares()
    {
        return [
            'serializes' => [],
            'orderbys'   => [],
            'timestamps' => ['createdTime'],
            'conditions' => [
                'userId = :userId',
                'app = :app',
                'inputsHash = :inputsHash',
            ]
        ];
    }
}
