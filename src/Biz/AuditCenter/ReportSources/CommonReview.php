<?php

namespace Biz\AuditCenter\ReportSources;

use Biz\Review\Dao\ReviewDao;
use Biz\Review\Service\ReviewService;

class CommonReview extends AbstractSource
{
    public function getReportContext($targetId)
    {
        $review = $this->getReviewService()->getReview($targetId);
        if (empty($review)) {
            return;
        }

        return [
            'content' => $review['content'],
            'author' => $review['userId'],
            'createdTime' => $review['createdTime'],
            'updatedTime' => $review['updatedTime'],
        ];
    }

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
