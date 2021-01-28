<?php
namespace Codeages\Biz\ItemBank\Answer\Dao\Impl;

use Codeages\Biz\ItemBank\Answer\Dao\AnswerSceneDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class AnswerSceneDaoImpl extends AdvancedDaoImpl implements AnswerSceneDao
{
    protected $table = 'biz_answer_scene';

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
                'updated_time'
            ],
            'orderbys' => [
                'created_time'
            ],
            'serializes' => [],
            'conditions' => [
               'id IN (:ids)'
            ],
        ];
    }
}
