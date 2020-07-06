<?php

namespace ApiBundle\Api\Resource\Review;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Review\ReviewException;
use Biz\Review\Service\ReviewService;
use Biz\User\Service\UserService;

class ReviewPost extends AbstractResource
{
    public function add(ApiRequest $request, $id)
    {
        $review = $this->getReviewService()->getReview($id);
        if (empty($review)) {
            throw ReviewException::NOT_FOUND_REVIEW();
        }

        $postNum = $this->getReviewService()->countReviews(['parentId' => $review['id']]);

        if ($postNum >= 5) {
            throw ReviewException::POST_LIMIT();
        }

        $reviewPost = [
            'content' => $request->request->get('content'),
            'targetType' => $review['targetType'],
            'targetId' => $review['targetId'],
            'rating' => $review['rating'],
            'userId' => $this->getCurrentUser()->getId(),
            'parentId' => $review['id'],
        ];

        $post = $this->getReviewService()->createReview($reviewPost);
        $post['user'] = $this->getUserService()->getUser($post['userId']);
        $post['template'] = $this->renderView('review/widget/subpost-item.html.twig', [
            'post' => $post,
            'author' => $this->getCurrentUser(),
            'canAccess' => true,
        ]);

        return $post;
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
}
