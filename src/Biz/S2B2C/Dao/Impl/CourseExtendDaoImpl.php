<?php

namespace Biz\S2B2C\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseExtendDaoImpl extends GeneralDaoImpl
{
    /**
     * @var string S端课程数据扩展表
     */
    protected $table = 's2b2c_course_v8_extend';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [],
            'orderbys' => [],
            'conditions' => [
                'id = :id',
                'courseId = :courseId',
            ],
        ];
    }
}
