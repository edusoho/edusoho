<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\ReplayActivityDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ReplayActivityDaoImpl extends AdvancedDaoImpl implements ReplayActivityDao
{
    protected $table = 'activity_replay';

    public function declares()
    {
        return [
            'timestamps' => ['created_time', 'updated_time'],
        ];
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByLessonId($lessonId)
    {
        return $this->findByFields(['origin_lesson_id' => $lessonId]);
    }
}
