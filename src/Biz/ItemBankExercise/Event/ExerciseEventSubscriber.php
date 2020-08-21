<?php

namespace Biz\ItemBankExercise\Event;

use AppBundle\Common\ArrayToolkit;
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
        $userId = $event->getArgument('userId');
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

        $this->synchronizationItemBankExercise($exercise, $questionBankTeacherIds, $userId);
    }

    private function synchronizationItemBankExercise($exercise, $questionBankTeacherIds, $userId)
    {
        $manage = new MemberManage($this->getBiz());
        $teacherMember = $manage->getMemberClass('teacher');
        $exerciseMembers = ArrayToolkit::index(
            $this->getExerciseMemberService()->search(['exerciseId' => $exercise['id']], [], 0, PHP_INT_MAX, ['id', 'userId', 'role']),
            'userId'
        );

        foreach ($questionBankTeacherIds as $questionBankTeacherId) {
            if (empty($exerciseMembers[$questionBankTeacherId])) {
                $teacherMember->join($exercise['id'], $questionBankTeacherId, ['remark' => '']);
            } else {
                if ('student' == $exerciseMembers[$questionBankTeacherId]['role']) {
                    $this->getExerciseMemberDao()->update($exerciseMembers[$questionBankTeacherId]['id'], ['role' => 'teacher']);
                }
            }
        }

        foreach ($exercise['teacherIds'] as $teacherId) {
            if (!in_array($teacherId, $questionBankTeacherIds)) {
                $this->getExerciseMemberDao()->update($exerciseMembers[$teacherId]['id'], ['role' => 'student']);
            }
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
