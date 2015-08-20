<?php
namespace Custom\Service\Homework\Dao;

interface ResultDao
{
    /**
     * 查找所有可以被userId互评作业答卷，其中包括未被老师点评过，且userId未曾互评过的其他童鞋的答卷.
     * @param homework 作业对象.
     * @param userId 参与互评的学员id.
     * @return 可以被互评的作业答卷id集合,.
     **/
    public function findPairReviewables($homework, $userId);

    /**
     * 根据courseId和userIdeas查找homework_result
     * @param $courseId
     * @param $userId
     * @return mixed
     */
    public function getResultByCourseIdAndUserId($courseId, $userId);

    /**
     * 查找可以提交的作业.
     * @return 作业集合.
     */
    public function findSubmitableResults();

    /**
     * 查找可以结算的作业.
     * @return 作业集合.
    **/
    public function findFinishableResults();
}