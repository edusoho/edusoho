<?php

namespace MarketingMallBundle\Api\Resource\MallProduct;

use ApiBundle\Api\Annotation\AuthClass;
use ApiBundle\Api\ApiRequest;
use Biz\Classroom\Service\ClassroomService;
use Biz\Common\CommonException;
use Biz\Course\Service\MemberService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use MarketingMallBundle\Api\Resource\BaseResource;

class MallProductMember extends BaseResource
{
    private $info = [
        'join' => ['remark' => '商城下单', 'reason' => '商城下单', 'reasonType' => '', 'reason_type' => ''],
        'exit' => ['reason' => '商城退款', 'reason_type' => 'refund', 'reasonType' => 'refund'],
    ];

    public function search(ApiRequest $request, $targetType)
    {
        $userId = $request->query->get('userId');
        $targetId = $request->query->get('targetId');
        $method = "checkIsExist{$targetType}";
        if (!method_exists($this, $method)){
            throw CommonException::NOTFOUND_METHOD();
        }

        return $this->$method($targetId, $userId);
    }

    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function add(ApiRequest $request, $targetType)
    {
        $params = $request->request->all();
        $userId = $params['userId'] ?? '';
        $targetId = $params['targetId'] ?? '';
        $method = "join".$targetType;
        if (!method_exists($this, $method)) {
            throw CommonException::NOTFOUND_METHOD();
        }
        $this->info['join']['reason_type'] = $this->info['join']['reasonType'] = 'true' == $params['isPaid'] ? 'buy_join' : 'free_join';
        $this->info['join']['expiryMode'] = $params['expiryMode'];
        $this->info['join']['expiryDays'] = $params['expiryDays'];

        return $this->$method($targetId, $userId);
    }

    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function remove(ApiRequest $request, $targetType)
    {
        $userId = $request->request->get('userId');
        $targetId = $request->request->get('targetId');
        $method = "exit{$targetType}";
        if (!method_exists($this, $method)) {
            throw CommonException::NOTFOUND_METHOD();
        }

        return $this->$method($targetId, $userId);
    }

    private function joinClassroom($targetId, $userId)
    {
        return $this->getClassroomService()->becomeStudent($targetId, $userId, $this->info['join']);
    }

    private function joinCourse($targetId, $userId)
    {
        return $this->getCourseMemberService()->becomeStudent($targetId, $userId, $this->info['join']);
    }

    private function joinQuestionBank($targetId, $userId)
    {
        return $this->getExerciseMemberService()->becomeStudent($targetId, $userId, $this->info['join']);
    }

    private function exitClassroom($targetId, $userId)
    {
        $this->getClassroomService()->removeStudent($targetId, $userId, $this->info['exit']);

        return true;
    }

    private function exitCourse($targetId, $userId)
    {
        $this->getCourseMemberService()->removeStudent($targetId, $userId, $this->info['exit']);

        return true;
    }

    private function exitQuestionBank($targetId, $userId)
    {
        $this->getExerciseMemberService()->removeStudent($targetId, $userId, $this->info['exit']);

        return true;
    }

    private function checkIsExistClassroom($targetId, $userId)
    {
        return !empty($this->getClassroomService()->getClassroomMember($targetId, $userId));
    }

    private function checkIsExistCourse($targetId, $userId)
    {
        return !empty($this->getCourseMemberService()->getCourseMember($targetId, $userId));
    }

    private function checkIsExistQuestionBank($targetId, $userId)
    {
        return !empty($this->getExerciseMemberService()->getExerciseMember($targetId, $userId));
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
