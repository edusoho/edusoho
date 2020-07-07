<?php

namespace ApiBundle\Api\Resource\Review;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Goods\Service\GoodsService;
use Biz\Review\Service\ReviewService;
use Biz\User\Service\UserService;

class Review extends AbstractResource
{
    protected $targetMap = [
        'course' => 'searchCourseInfo',
        'goods' => 'searchGoodsInfo',
    ];

    /**
     * @return mixed
     *
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        list($offset, $limit) = $this->getOffsetAndLimit($request);

        $orderBys = empty($request->query->get('orderBys')) ? ['createdTime' => 'DESC'] : $request->query->get('orderBys');

        $reviews = $this->getReviewService()->searchReviews($request->query->all(), $orderBys, $offset, $limit);

        return $this->dealReviews($reviews);
    }

    public function add(ApiRequest $request)
    {
        $review = [
            'targetType' => $request->request->get('targetType'),
            'targetId' => $request->request->get('targetId'),
            'userId' => $request->request->get('userId'),
            'content' => $request->request->get('content'),
            'rating' => $request->request->get('rating'),
        ];

        $review['userId'] = empty($review['userId']) ? $this->getCurrentUser()->getId() : $review['userId'];

        $existed = $this->getReviewService()->getByUserIdAndTargetTypeAndTargetId($review['userId'], $request->request->get('targetType'), $request->request->get('targetId'));

        if (!empty($existed['id'])) {
            return $this->getReviewService()->updateReview($existed['id'], $review);
        }

        return $this->getReviewService()->createReview($review);
    }

    public function remove(ApiRequest $request, $id)
    {
        return $this->getReviewService()->deleteReview($id);
    }

    protected function dealReview($review)
    {
        $review['user'] = $this->getUserService()->getUser($review['userId']);
        $targetInfo = $this->getReviewTargetInfoByTargetTypeAndTargetIds($review['targetType'], [$review['targetId']]);

        $review['targetName'] = empty($targetInfo['title']) ? null : $targetInfo['title'];

        return $review;
    }

    protected function dealReviews($reviews)
    {
        $targetInfo = [];
        $reviewGroupByType = ArrayToolkit::group($reviews, 'targetType');
        foreach ($reviewGroupByType as $type => $groupedReviews) {
            $targetIds = array_unique(ArrayToolkit::column($groupedReviews, 'targetId'));
            $targetInfo[$type] = $this->getReviewTargetInfoByTargetTypeAndTargetIds($type, array_values($targetIds));
        }

        $userInfo = $this->getReviewUserInfoByUserIds(ArrayToolkit::column($reviews, 'userId'));

        foreach ($reviews as &$review) {
            $review['user'] = empty($userInfo[$review['userId']]) ? null : $userInfo[$review['userId']];
            $review['targetName'] = empty($targetInfo[$review['targetType']][$review['targetId']]) ? '' : $targetInfo[$review['targetType']][$review['targetId']]['title'];
        }

        return $reviews;
    }

    protected function getReviewUserInfoByUserIds($userIds)
    {
        return $this->getUserService()->findUsersByIds($userIds);
    }

    protected function getReviewTargetInfoByTargetTypeAndTargetIds($targetType, $targetIds)
    {
        if (!in_array($targetType, array_keys($this->targetMap))) {
            return null;
        }

        $function = $this->targetMap[$targetType];

        return $this->$function($targetIds);
    }

    protected function searchGoodsInfo($ids)
    {
        $goods = $this->getGoodsService()->searchGoods(['ids' => $ids], [], 0, count($ids), ['id', 'title']);

        return ArrayToolkit::index($goods, 'id');
    }

    protected function searchCourseInfo($ids)
    {
        $courses = $this->getCourseService()->searchCourses(['ids' => $ids], [], 0, count($ids), ['id', 'title']);

        return ArrayToolkit::index($courses, 'id');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->service('Review:ReviewService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->service('Goods:GoodsService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}
