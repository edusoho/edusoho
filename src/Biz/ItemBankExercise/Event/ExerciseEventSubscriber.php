<?php

namespace Biz\ItemBankExercise\Event;

use Biz\ItemBankExercise\Dao\ExerciseMemberDao;
use Biz\ItemBankExercise\Member\MemberManage;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
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

        if ($members) {
            $questionBankTeacherIds = explode(',', $members);
            if (!in_array($exercise['creator'], $questionBankTeacherIds)) {
                $questionBankTeacherIds[] = $exercise['creator'];
            }
        } else {
            $questionBankTeacherIds = [$exercise['creator']];
        }

        $this->synchronizationItemBankExercise($exercise, $questionBankTeacherIds);
    }

    private function synchronizationItemBankExercise($exercise, $questionBankTeacherIds)
    {
        $manage = new MemberManage($this->getBiz());
        $teacherMember = $manage->getMemberClass('teacher');
        $exerciseTeachers = $this->getExerciseMemberService()->search(['exerciseId' => $exercise['id'], 'role' => 'teacher'], [], 0, PHP_INT_MAX, ['userId']);
        $exerciseTeacherIds = array_column($exerciseTeachers, 'userId');
        foreach (array_diff($questionBankTeacherIds, $exerciseTeacherIds) as $teacherId) {
            $teacherMember->join($exercise['id'], $teacherId, ['remark' => '']);
        }

        $toDeleteTeacherIds = array_diff($exerciseTeacherIds, $questionBankTeacherIds);
        if (!empty($toDeleteTeacherIds)) {
            $this->getExerciseMemberDao()->batchDelete(['exerciseId' => $exercise['id'], 'userIds' => $toDeleteTeacherIds, 'role' => 'teacher']);
        }

        $this->getExerciseService()->update($exercise['id'], ['teacherIds' => $questionBankTeacherIds]);
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

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return ExerciseMemberDao
     */
    protected function getExerciseMemberDao()
    {
        return $this->getBiz()->dao('ItemBankExercise:ExerciseMemberDao');
    }
}
