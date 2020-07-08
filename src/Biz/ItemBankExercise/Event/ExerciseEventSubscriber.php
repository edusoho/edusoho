<?php

namespace Biz\ItemBankExercise\Event;

use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\Review\Service\ReviewService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExerciseEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'questionBank.update' => 'onQuestionBankUpdate',

            'review.create' => 'onReviewChanged',
            'review.update' => 'onReviewChanged',
            'review.delete' => 'onReviewChanged',
        ];
    }

    public function onReviewChanged(Event $event)
    {
        $review = $event->getSubject();

        if ('item_bank_exercise' != $review['targetType']) {
            return false;
        }

        $ratingFields = $this->getReviewService()->countRatingByTargetTypeAndTargetId($review['targetType'], $review['targetId']);
        $this->getExerciseService()->update($review['targetId'], $ratingFields);
    }

    public function onQuestionBankUpdate(Event $event)
    {
        $questionBank = $event->getSubject();
        $exercise = $this->getExerciseService()->getByQuestionBankId($questionBank['id']);

        $this->getExerciseService()->update(
            $exercise['id'],
            [
                'categoryId' => $questionBank['categoryId'],
                'title' => $questionBank['name'],
            ]
        );
    }

    /**
     * @return ReviewService
     */
    protected function getReviewService()
    {
        return $this->getBiz()->service('Review:ReviewService');
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
    }
}
