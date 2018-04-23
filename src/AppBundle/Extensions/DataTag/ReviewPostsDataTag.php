<?php

namespace AppBundle\Extensions\DataTag;

class ReviewPostsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取一个课程评论.
     *
     * 可传入的参数：
     *   reviewId   必需 课程评论ID
     *   targetType 必须 评价对象类型
     *   start      必需 起始值
     *   limit      必需 数量
     *
     * @param array $arguments 参数
     *
     * @return array 课程评论
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['reviewId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('reviewId参数缺失'));
        }

        $targetType = empty($arguments['targetType']) ? 'course' : $arguments['targetType'];
        $start = empty($arguments['start']) ? 0 : intval($arguments['start']);
        $limit = empty($arguments['limit']) ? 5 : intval($arguments['limit']);

        if ($targetType == 'classroom') {
            return $this->getClassroomReviewService()->searchReviews(array('parentId' => $arguments['reviewId']), array('createdTime' => 'ASC'), $start, $limit);
        } else {
            return $this->getCourseReviewService()->searchReviews(array('parentId' => $arguments['reviewId']), array('createdTime' => 'ASC'), $start, $limit);
        }
    }

    protected function getCourseReviewService()
    {
        return $this->createService('Course:ReviewService');
    }

    private function getClassroomReviewService()
    {
        return $this->createService('Classroom:ClassroomReviewService');
    }
}
