<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\ItemBankExercise\ItemBankExerciseMemberException;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;

class MeItemBankExerciseMember extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Classroom\ClassroomMemberFilter", mode="simple")
     */
    public function get(ApiRequest $request, $exerciseId)
    {
        $exerciseMember = $this->getExerciseMemberService()->getExerciseMember($exerciseId, $this->getCurrentUser()->getId());

        if ($exerciseMember) {
            $exerciseMember['access'] = $this->getExerciseService()->canLearnExercise($exerciseId);
            $exerciseMember['expire'] = $this->getExerciseMemberExpire($exerciseMember);
        }

        return $exerciseMember;
    }

    public function remove(ApiRequest $request, $exerciseId)
    {
        $note = $this->filterUtf8mb4($request->query->get('note')) ? $this->filterUtf8mb4($request->query->get('note')) : '从App退出题库练习';

        $exercise = $this->getExerciseService()->get($exerciseId);
        $user = $this->getCurrentUser();
        $member = $user['id'] ? $this->getExerciseMemberService()->getExerciseMember($exercise['id'], $user['id']) : null;
        if (empty($member)) {
            throw ItemBankExerciseMemberException::NOTFOUND_MEMBER();
        }

        $this->getExerciseMemberService()->removeStudent($exercise['id'], $user['id'], [
            'reason' => $note,
            'reasonType' => 'exit',
        ]);

        return ['success' => true];
    }

    private function getExerciseMemberExpire($exerciseMember)
    {
        $exercise = $this->getExerciseService()->get($exerciseMember['exerciseId']);
        if (empty($exercise) || empty($exerciseMember)) {
            return [
                'status' => 0,
                'deadline' => 0,
            ];
        }

        if ('forever' == $exercise['expiryMode']) {
            return [
                'status' => 1,
                'deadline' => $exerciseMember['deadline'],
            ];
        }

        $deadline = $exerciseMember['deadline'];

        // 比较:学员有效期和课程有效期
        $exerciseDeadline = $this->getExerciseDeadline($exercise);
        if ($exerciseDeadline) {
            $deadline = $deadline < $exerciseDeadline ? $deadline : $exerciseDeadline;
        }

        return [
            'status' => $deadline < time() ? 0 : 1,
            'deadline' => $deadline,
        ];
    }

    private function getExerciseDeadline($exercise)
    {
        $deadline = 0;
        if ('date' == $exercise['expiryMode'] || 'end_date' == $exercise['expiryMode']) {
            $deadline = $exercise['expiryEndDate'];
        }

        return $deadline;
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->service('ItemBankExercise:ExerciseMemberService');
    }
}
