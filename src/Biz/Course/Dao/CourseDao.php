<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseDao extends GeneralDaoInterface
{
    const TABLE_NAME = 'course_v8';

    public function findCoursesByCourseSetIdAndStatus($courseSetId, $status);

    public function getDefaultCourseByCourseSetId($courseSetId);

    public function getDefaultCoursesByCourseSetIds($courseSetIds);

    public function findByCourseSetIds(array $setIds);

    public function findPriceIntervalByCourseSetIds($courseSetIds);

    public function findCoursesByIds($ids);

    public function findCourseSetIncomesByCourseSetIds(array $courseSetIds);

    public function analysisCourseDataByTime($startTime, $endTime);

    public function findCoursesByParentIdAndLocked($parentId, $locked);

    public function findCoursesByParentIds($parentIds);

    public function getMinAndMaxPublishedCoursePriceByCourseSetId($courseSetId);

    public function updateMaxRateByCourseSetId($courseSetId, $updateFields);

    public function updateCourseRecommendByCourseSetId($courseSetId, $fields);

    public function updateCategoryByCourseSetId($courseSetId, $fields);

    public function countGroupByCourseSetIds($courseSetIds);

    public function searchWithJoinCourseSet($conditions, $orderBys, $start, $limit);

    public function searchByStudentNumAndTimeZone($conditions, $start, $limit);

    public function searchByRatingAndTimeZone($conditions, $start, $limit);

    public function countWithJoinCourseSet($conditions);
}
