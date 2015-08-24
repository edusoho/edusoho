<?php

namespace Custom\Service\Homework\Dao\Impl;

use Custom\Service\Homework\Dao\HomeworkItemResultDao;
use Homework\Service\Homework\Dao\Impl\HomeworkItemResultDaoImpl as BaseHomeworkItemResultDao;

class HomeworkItemResultDaoImpl extends BaseHomeworkItemResultDao implements HomeworkItemResultDao
{
    protected $table = 'homework_item_result';


}
