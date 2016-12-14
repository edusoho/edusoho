<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseMemberDao extends GeneralDaoInterface
{
    public function getMemberByCourseIdAndUserId($courseId, $userId);

    public function findStudentsByCourseId($courseId);

    /**
     * 用来替代各种命名复杂的关联表的列表查询
     *
     * @param $conditions
     * @param $orderBy
     * @param $start
     * @param $limit
     * @return mixed
     */
    public function searchMemberFetchCourse($conditions, $orderBy, $start, $limit);

    /**
     * 用来替代各种命名复杂的关联表的数量查询
     * @param $conditions
     * @return mixed
     */
    public function countMemberFetchCourse($conditions);

    public function findLearnedCoursesByCourseIdAndUserId($courseId, $userId);

}
