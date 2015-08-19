<?php

namespace Custom\Service\Homework\Dao;

/**
 * 点评明细dao.
 * 包括老师评分和学生互评.
**/
interface ReviewItemDao
{

    /**
     * 创建作业点评.
     * @param item 点评明细数据.
     * @return 保存后的点评明细数据.
    **/
    public function create($item);

    /**
     * 获取一个作业点评.
    **/
    public function get($id);
}