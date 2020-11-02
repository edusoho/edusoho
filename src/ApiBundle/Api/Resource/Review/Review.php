<?php

namespace ApiBundle\Api\Resource\Review;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Goods\Service\GoodsService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\Review\Service\ReviewService;
use Biz\User\Service\UserService;

class Review extends AbstractResource
{
    protected $targetMap = [
        'course' => 'searchCourseInfo',
        'goods' => 'searchGoodsInfo',
        'item_bank_exercise' => 'searchItemBankExericseInfo',
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

        $conditions = array_merge(['parentId' => 0], $request->query->all());
        $reviews = $this->getReviewService()->searchReviews($conditions, $orderBys, $offset, $limit);

        $reviews = $this->makeUpReviews($reviews, $request->query->get('needPosts'));

        return $this->makePagingObject($reviews, $this->getReviewService()->countReviews($conditions), $offset, $limit);
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

        $existed = $this->getReviewService()->getReviewByUserIdAndTargetTypeAndTargetId($review['userId'], $request->request->get('targetType'), $request->request->get('targetId'));

        if (!empty($existed['id'])) {
            $review = $this->getReviewService()->updateReview($existed['id'], $review);
        } else {
            $review = $this->getReviewService()->createReview($review);
        }

        $this->getOCUtil()->single($review, ['userId'], 'user');
        $this->getOCUtil()->single($review, ['targetId'], $review['targetType']);

        return $review;
    }

    public function remove(ApiRequest $request, $id)
    {
        return $this->getReviewService()->deleteReview($id);
    }

    protected function makeUpReviews($reviews, $needPosts = false)
    {
        $makeUpReviews = [];
        $reviewGroupByType = ArrayToolkit::group($reviews, 'targetType');
        foreach ($reviewGroupByType as $type => $groupedReviews) {
            $this->getOCUtil()->multiple($groupedReviews, ['targetId'], $type);
            $this->getOCUtil()->multiple($groupedReviews, ['userId'], 'user');

            $makeUpReviews = array_merge($makeUpReviews, $groupedReviews);
        }

        if (!$needPosts) {
            return $makeUpReviews;
        }

        foreach ($makeUpReviews as &$review) {
            $review['posts'] = $this->getReviewService()->searchReviews(
                ['parentId' => $review['id']],
                ['createdTime' => 'ASC'],
                0,
                5
            );
            $this->getOCUtil()->multiple($review['posts'], ['userId'], 'user');
        }

        return $makeUpReviews;
    }

    protected function searchItemBankExericseInfo($ids)
    {
        $itemBankExercises = $this->getItemBankExerciseService()->search(['ids' => $ids], [], 0, count($ids), ['id', 'title']);

        return ArrayToolkit::index($itemBankExercises, 'id');
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

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }
}
