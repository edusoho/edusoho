<?php

namespace ApiBundle\Api\Resource\Review;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Review\Service\ReviewService;

class Review extends AbstractResource
{
    /**
     * @param $id
     *
     * @return mixed
     *
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $id)
    {
        return $this->getReviewService()->getReview($id);
    }

    /**
     * @return mixed
     *
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = [
            'targetId' => $request->query->get('targetId'),
            'targetType' => $request->query->get('targetType'),
        ];

        list($offset, $limit) = $this->getOffsetAndLimit($request);

        return $this->getReviewService()->searchReview($conditions, ['createdTime' => 'DESC'], $offset, $limit);
    }

    public function add(ApiRequest $request)
    {
        return $this->getReviewService()->createReview($request->request->all());
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->getBiz()->service('Review:ReviewService');
    }
}
