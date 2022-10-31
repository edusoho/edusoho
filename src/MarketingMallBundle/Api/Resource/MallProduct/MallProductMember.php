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
    private $info = ['remark' => '商城下单'];

    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function add(ApiRequest $request, $targetType)
    {
        $userId = $request->request->get("userId");
        $targetId = $request->request->get("targetId");
        $method = "join".$targetType;
        if (!method_exists($this, $method)) {
            throw CommonException::NOTFOUND_METHOD();
        }
        return $this->$method($targetId, $userId);
    }

    private function joinClassroom($targetId, $userId)
    {
        return $this->getClassroomService()->becomeStudent($targetId, $userId, $this->info);
    }

    private function joinCourse($targetId, $userId)
    {
        return $this->getCourseMemberService()->becomeStudent($targetId, $userId, $this->info);
    }

    private function joinQuestionBank($targetId, $userId)
    {
        return $this->getExerciseMemberService()->becomeStudent($targetId, $userId, $this->info);
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
