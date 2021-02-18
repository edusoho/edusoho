<?php

namespace Codeages\Biz\ItemBank\FaceInspection\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\ItemBank\FaceInspection\Dao\RecordDao;

class RecordDaoImpl extends AdvancedDaoImpl implements RecordDao
{
    protected $table = 'biz_facein_cheat_record';

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
            ],
            'orderbys' => [
                'id',
                'created_time',
            ],
            'conditions' => [
                'user_id = :user_id',
                'user_id in (:user_ids)',
                'answer_scene_id = :answer_scene_id',
                'answer_record_id = :answer_record_id',
                'answer_record_id in (:answer_record_ids)',
            ],
        ];
    }
}
