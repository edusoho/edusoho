<?php

namespace Biz\AuditCenter\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\AuditCenter\Service\ContentAuditService;
use Biz\AuditCenter\Service\ReportAuditService;
use Biz\Goods\Service\GoodsService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserContentAuditEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'review.delete' => 'onReviewDelete',
        ];
    }

    public function onReviewDelete(Event $event)
    {
        $review = $event->getSubject();
        $reviewAuditTargetType = $this->getReviewAuditTargetType($review);

        $userAudit = $this->getContentAuditService()->getAuditByTargetTypeAndTargetId($reviewAuditTargetType, $review['id']);
        if ($userAudit) {
            $this->getContentAuditService()->deleteAudit($userAudit['id']);
        }

        $reportAudits = $this->getReportAuditService()->findReportAuditsByTargetTypeAndTargetId($reviewAuditTargetType, $review['id']);
        if ($reportAudits) {
            $this->getReportAuditService()->deleteReportAuditsByIds(ArrayToolkit::column($reportAudits, 'id'));
        }
    }

    private function getReviewAuditTargetType($review)
    {
        if ('goods' === $review['targetType']) {
            $goods = $this->getGoodsService()->getGoods($review['targetId']);
            if ('course' === $goods['type']) {
                $reviewTargetType = empty($review['parentId']) ? 'course_review' : 'course_review_reply';
            } elseif ('classroom' === $goods['type']) {
                $reviewTargetType = empty($review['parentId']) ? 'classroom_review' : 'classroom_review_reply';
            } else {
                $reviewTargetType = '';
            }
        } elseif ('course' === $review['targetType']) {
            $reviewTargetType = empty($review['parentId']) ? 'course_review' : 'course_review_reply';
        } elseif ('item_bank_exercise' === $review['targetType']) {
            $reviewTargetType = empty($review['parentId']) ? 'item_bank_exercise_review' : 'item_bank_exercise_review_reply';
        } else {
            $reviewTargetType = '';
        }

        return $reviewTargetType;
    }

    /**
     * @return ContentAuditService
     */
    public function getContentAuditService()
    {
        return $this->getBiz()->service('AuditCenter:ContentAuditService');
    }

    /**
     * @return ReportAuditService
     */
    public function getReportAuditService()
    {
        return $this->getBiz()->service('AuditCenter:ReportAuditService');
    }

    /**
     * @return GoodsService
     */
    public function getGoodsService()
    {
        return $this->getBiz()->service('Goods:GoodsService');
    }
}
