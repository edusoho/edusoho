<?php

namespace AppBundle\Extensions\DataTag;

class TopRatingCourseReviewsDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取按照评分排行的课程评论.
     *
     * 可传入的参数：
     *   courseId 必需 课程ID
     *   count 必需 课程话题数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 课程评论
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);
        $conditions = $this->checkCourseArguments($arguments);
        $courseReviews = $this->getReviewService()->searchReviews($conditions, $sort = 'rating', 0, $arguments['count']);

        return $this->getCoursesAndUsers($courseReviews);
    }
}
