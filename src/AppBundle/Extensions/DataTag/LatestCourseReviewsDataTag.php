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

        $defaultConditions = [
            'parentId' => 0,
            'targetType' => 'course',
        ];

        if (isset($conditions['courseId'])) {
            $conditions['targetId'] = $conditions['courseId'];
            unset($conditions['courseId']);
            $conditions = array_merge($defaultConditions, $conditions);
        } else {
            $conditions = array_merge($defaultConditions, $conditions);
        }

        $courseReviews = $this->getReviewService()->searchReviews($conditions, ['createdTime' => 'DESC'], 0, $arguments['count']);

        return $this->getCoursesAndUsers($courseReviews);
    }
}
