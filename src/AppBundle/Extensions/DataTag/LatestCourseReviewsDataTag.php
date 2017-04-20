<?php

namespace AppBundle\Extensions\DataTag;

class LatestCourseReviewsDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取最新发表的课程评论列表.
     *
     * 可传入的参数：
     *   courseId 可选 课程ID
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
        $conditions = array(
            'private' => 0,
            'parentId' => 0,
        );
        $courseReviews = $this->getReviewService()->searchReviews($conditions, $sort = 'latest', 0, $arguments['count']);

        return $this->getCoursesAndUsers($courseReviews);
    }
}
