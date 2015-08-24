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

    /**
     * 计算答卷下所有答题的用户互评平均分.
     * @param resultId 答卷id.
     * @param 答题平均分集合, 如[{'homeworkItemResultId': 1, 'score': 1.5}].
    **/
    public function averageItemScores($resultId);

    /**
     * 根据作业答卷id查找该答卷下的所有点评记录.
     * @param resultId 答卷id.
     * @return 点评记录集合.
    **/
    public function findItemsByResultId($resultId);
}