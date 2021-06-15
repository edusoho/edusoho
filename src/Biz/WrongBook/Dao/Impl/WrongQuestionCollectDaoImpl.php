<?php


namespace Biz\WrongBook\Dao\Impl;


use Biz\WrongBook\Dao\WrongQuestionCollectDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class WrongQuestionCollectDaoImpl extends AdvancedDaoImpl implements WrongQuestionCollectDao
{
    protected $table = 'biz_wrong_question_collect';

    public function declares()
    {
        return [
            'conditions' => [
                'id = :id',
                'createdTime = :createdTime',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }
}