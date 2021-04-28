<?php

namespace Biz\AuditCenter\ContentAuditSources;

use Biz\Review\Dao\ReviewDao;
use Biz\Review\Service\ReviewService;

class CommonReview extends AbstractSource
{
    public function handleSource($audit)
    {
        $review = $this->getReviewService()->getReview($audit['targetId']);
        if (empty($review)) {
            return;
        }
        $fields = $this->getAuditFields($audit);

        if (!empty($fields)) {
            $this->getReviewDao()->update($review['id'], $fields);
        }
    }

    /**
     * @return ReviewService
     */
    public function getReviewService()
    {
        return $this->biz->service('Review:ReviewService');
    }

    /**
     * @return ReviewDao
     */
    public function getReviewDao()
    {
        return $this->biz->dao('Review:ReviewDao');
    }
}
