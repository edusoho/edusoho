<?php

namespace Custom\Service\LectureNote\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseMaterialDao;

class CourseMaterialDaoImpl extends BaseDao implements CourseMaterialDao
{
    protected $table = 'lecture_note';

    public function FunctionName($value='')
    {
        
    }
}