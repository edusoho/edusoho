<?php

namespace Biz\SCRM\GoodsMediator;

use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\MemberService;
use Biz\ItemBankExercise\OperateReason;

class Classroom extends AbstractMediator
{
    public function join($user, $specs, $context = [])
    {
        $orderInfo = $context['orderInfo'];
        $classroomMember = $this->getClassroomService()->getClassroomMember($specs['targetId'], $user['id']);
        if (empty($classroomMember)) {
            $data = [
                'price' => $orderInfo['payAmount'],
                'remark' => '通过SCRM添加',
                'source' => 'outside',
                'reason' => OperateReason::JOIN_BY_IMPORT,
                'reasonType' => 'import_join',
            ];
            $this->getClassroomService()->becomeStudentWithOrder($specs['targetId'], $user['id'], $data);
        }
    }

    /**
     * @return MemberService
     */
    protected function getClassroomMemberService()
    {
        return $this->biz->service('Classroom:MemberService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
