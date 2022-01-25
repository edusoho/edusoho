<?php

namespace Biz\SCRM\GoodsMediator;

use Biz\Course\Service\MemberService;
use Biz\ItemBankExercise\OperateReason;

class Course extends AbstractMediator
{
    /**
     * @param $user
     * @param $specs
     * @param array $context [array orderInfo, ]
     */
    public function join($user, $specs, $context = [])
    {
        $orderInfo = $context['orderInfo'];
        $courseMember = $this->getCourseMemberService()->getCourseMember($specs['targetId'], $user['id']);
        if (empty($courseMember)) {
            $data = [
                'price' => $orderInfo['payAmount'],
                'remark' => '通过SCRM添加',
                'source' => 'outside',
                'reason' => OperateReason::JOIN_BY_IMPORT,
                'reasonType' => OperateReason::JOIN_BY_IMPORT_TYPE,
                'isAdminAdded' => 1,
                'joinType' => 'SCRM',
            ];
            $this->getCourseMemberService()->becomeStudentAndCreateOrder($user['id'], $specs['targetId'], $data);
        }
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }
}
