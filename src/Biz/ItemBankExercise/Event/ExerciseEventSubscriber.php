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

        $this->synchronizationItemBankExercise($exercise, $members, $userId);
    }

    private function synchronizationItemBankExercise($exercise, $members, $userId)
    {
        $manage = new MemberManage($this->getBiz());
        $teacherMember = $manage->getMemberClass('teacher');
        $exerciseMembers = $this->getExerciseMemberService()->search(['exerciseId' => $exercise['id']], [], 0, PHP_INT_MAX);
        $exerciseMembers = ArrayToolkit::index($exerciseMembers, 'userId');
        if (empty($members)) {
            if (isset($exerciseMembers[$userId])) {
                $this->getExerciseMemberDao()->update($exerciseMembers[$userId]['id'], ['role' => 'teacher']);
                unset($exerciseMembers[$userId]);
                $this->getExerciseMemberDao()->update(['ids' => ArrayToolkit::column($exerciseMembers, 'id')], ['role' => 'student']);
            } else {
                $teacherMember->join($exercise['id'], $userId, ['remark' => '']);
            }
        } else {
            $members = explode(',', $members);
            $oldTeacherIds = $exercise['teacherIds'];
            if (count($members) >= count($oldTeacherIds)) {
                foreach ($members as $teacherId) {
                    if (!in_array($teacherId, $oldTeacherIds)) {
                        if (isset($exerciseMembers[$teacherId])) {
                            $this->getExerciseMemberDao()->update($exerciseMembers[$teacherId]['id'], ['role' => 'teacher']);
                        } else {
                            $teacherMember->join($exercise['id'], $teacherId, ['remark' => '']);
                        }
                    }
                }
            } else {
                foreach ($oldTeacherIds as $oldTeacherId) {
                    if (!in_array($oldTeacherId, $members)) {
                        $this->getExerciseMemberDao()->update(['userId' => $oldTeacherId, 'exerciseId' => $exercise['id']], ['role' => 'student']);
                    }
                }
            }
        }

        $teacherIds = $this->getExerciseMemberService()->search(['exerciseId' => $exercise['id'], 'role' => 'teacher'], [], 0, PHP_INT_MAX);
        $this->getExerciseService()->update($exercise['id'], ['teacherIds' => ArrayToolkit::column($teacherIds, 'userId')]);
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
