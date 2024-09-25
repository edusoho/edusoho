<?php

namespace Biz\AI\Dao\Impl;

use Biz\AI\Dao\AIAnswerResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AIAnswerResultDaoImpl extends GeneralDaoImpl implements AIAnswerResultDao
{
    protected $table = 'ai_answer_result';

    public function findByAppAndInputsHash($app, $inputsHash)
    {
        return $this->findByFields(['app' => $app, 'inputsHash' => $inputsHash]);
    }

    public function declares()
    {
        return [
            'serializes' => [],
            'orderbys'   => [],
            'timestamps' => ['createdTime'],
            'conditions' => [
            ]
        ];
    }
}
