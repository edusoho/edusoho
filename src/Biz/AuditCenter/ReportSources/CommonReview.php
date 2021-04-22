<?php

namespace Biz\AuditCenter\ReportSources;

use Biz\Review\Service\ReviewService;

class CommonReview extends AbstractSource
{
    public function getReportContext($targetId)
    {
        $review = $this->getReviewService()->getReview($targetId);

        return [
            'content' => $review['content'],
            'author' => $review['userId'],
            'createdTime' => $review['createdTime'],
        ];
    }

    /**
     * @return ReviewService
     */
    public function getReviewService()
    {
        return $this->biz->service('Review:ReviewService');
    }
}
