<?php

namespace Custom\Service\Homework\Dao;

interface ReviewDao
{

    /**
     * 创建作业点评.
     * @param review 点评数据.
     * @return 保存后的点评数据.
    **/
    public function create($review);

    /**
     * 获取一个作业点评.
    **/
    public function get($id);
}