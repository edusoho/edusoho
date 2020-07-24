<?php

namespace Biz\ItemBankExercise\Event;

use Biz\ItemBankExercise\Member\MemberManage;
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
        $members = $event->getArgument('members');
        $exercise = $this->getExerciseService()->getByQuestionBankId($questionBank['id']);
        if (empty($exercise)) {
            return true;
        }

        $this->getExerciseService()->update(
            $exercise['id'],
            [
                'categoryId' => $questionBank['categoryId'],
                'title' => $questionBank['name'],
            ]
        );

        $this->synchronizationItemBankExercise($exercise, $members);
    }

    protected function synchronizationItemBankExercise($exercise, $members)
    {
        $members = explode(',', $members);

        $oldTeacherIds = $exercise['teacherIds'];
        $manage = new MemberManage($this->getBiz());
        $teacherMember = $manage->getMemberClass('teacher');
        foreach ($members as $teacherId) {
            if (!in_array($teacherId, $oldTeacherIds)) {
                $teacherMember->join($exercise['id'], $teacherId, ['remark' => '']);
            }
        }
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
