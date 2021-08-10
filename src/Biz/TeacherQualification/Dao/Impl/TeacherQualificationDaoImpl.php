<?php

namespace Biz\TeacherQualification\Dao\Impl;

use Biz\TeacherQualification\Dao\TeacherQualificationDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TeacherQualificationDaoImpl extends GeneralDaoImpl implements TeacherQualificationDao
{
    protected $table = 'teacher_qualification';

    public function getByUserId($userId)
    {
        return $this->getByFields(['user_id' => $userId]);
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
                'updated_time',
            ],
            'orderbys' => [
                'updated_time',
                'created_time',
            ],
        ];
    }
}
