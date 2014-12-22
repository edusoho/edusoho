<?php

namespace Topxia\Service\Task\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Task\Dao\TaskDao;

class TaskDaoImpl extends BaseDao implements TaskDao 
{
    protected $table = 'task';
    private $serializeFields = array(
        'tagIds' => 'json',
    );

}