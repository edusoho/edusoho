<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Review\Service\ReviewService;

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

        return $this->getReviewService()->searchReviews(
            ['targetType' => $targetType, 'parentId' => $arguments['reviewId']],
            ['createdTime' => 'ASC'],
            $start, $limit
        );
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->createService('Review:ReviewService');
    }
}
