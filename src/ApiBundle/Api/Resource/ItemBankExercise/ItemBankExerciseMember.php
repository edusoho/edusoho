<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\MemberOperation\Service\MemberOperationService;
use Biz\User\Service\UserService;

class ItemBankExerciseMember extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $exerciseId)
    {
        $conditions['role'] = 'student';
        $conditions['exerciseId'] = $exerciseId;
        $conditions['locked'] = 0;

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $conditions = ArrayToolkit::parts($request->query->all(), [
            'startTimeGreaterThan',
            'startTimeLessThan',
            'joinedChannel',
            'deadlineAfter',
            'deadlineBefore',
            'userKeyword',
        ]);
        $conditions['exerciseId'] = $exerciseId;
        if (isset($conditions['userKeyword']) && '' != $conditions['userKeyword']) {
            $conditions['userIds'] = $this->getUserService()->getUserIdsByKeyword($conditions['userKeyword']);
            unset($conditions['userKeyword']);
        }
        $members = $this->getItemBankExerciseMemberService()->search(
            $conditions,
            ['createdTime' => 'DESC'],
            $offset,
            $limit
        );

        foreach ($members as &$member) {
            $member['user'] = empty($users[$member['userId']]) ? null : $users[$member['userId']];
            $member['joinedChannelText'] = $this->convertJoinedChannel($member);
        }

        $total = $this->getItemBankExerciseMemberService()->count($conditions);

        $this->getOCUtil()->multiple($members, ['userId']);

        return $this->makePagingObject($members, $total, $offset, $limit);
    }

    public function add(ApiRequest $request, $exerciseId)
    {
        $member = $this->getItemBankExerciseMemberService()->getExerciseMember($exerciseId, $this->getCurrentUser()->getId());

        if (!$member) {
            $member = $this->getItemBankExerciseService()->freeJoinExercise($exerciseId);
        }

        $this->getOCUtil()->single($member, ['userId']);

        return $member;
    }

    private function convertJoinedChannel($member)
    {
        if ('import_join' === $member['joinedChannel']) {
            $records = $this->getMemberOperationService()->searchRecords(['target_type' => 'classroom', 'target_id' => $member['classroomId'], 'member_id' => $member['id'], 'operate_type' => 'join'], ['id' => 'DESC'], 0, 1);
            if (!empty($records)) {
                $operator = $this->getUserService()->getUser($records[0]['operator_id']);

                return "{$operator['nickname']}添加";
            }
        }

        return ['free_join' => '免费加入', 'buy_join' => '购买加入', 'bind_join' => '绑定加入'][$member['joinedChannel']] ?? '';
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseMemberService
     */
    protected function getItemBankExerciseMemberService()
    {
        return $this->service('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return MemberOperationService
     */
    private function getMemberOperationService()
    {
        return $this->service('MemberOperation:MemberOperationService');
    }
}
