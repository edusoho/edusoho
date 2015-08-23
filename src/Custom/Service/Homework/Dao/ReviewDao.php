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
     * @param id 点评id.
     * @return 点评数据.
    **/
    public function get($id);

    /**
     * 统计用户的作业互评数量.
     * @param homeworkId, 作业id.
     * @param userId 用户id.
     * @return 用户作业互评数量.
    **/
    public function countUserPairReviews($homeworkId, $userId);

    /**
     * 根据作业答卷id查找作业互评.
     * @param resultId 作业答卷id.
     * @return 互评记录集合.
    **/
    public function findReviewsByResultId($resultId);
}