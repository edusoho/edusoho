<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Service\ClassroomService;
use ApiBundle\Api\Annotation\ResponseFilter;

class ClassroomMarketingMember extends AbstractResource
{
    /**
     * @param ApiRequest $request
     * @param $classroomId
     *
     * @return array
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function add(ApiRequest $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        if (empty($classroom)) {
            throw ClassroomException::NOTFOUND_CLASSROOM();
        }
        $biz = $this->getBiz();
        $logger = $biz['logger'];
        $logger->info('微营销通知处理班级订单');
        $postData = $request->request->all();

        try {
            return $this->getMarketingClassroomService()->join($postData);
        } catch (\Exception $e) {
            $logger->error($e);

            return array('code' => 'error', 'msg' => 'ES处理微营销订单失败,'.$e->getTraceAsString());
        }
    }

    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Classroom\ClassroomMemberFilter", mode="public")
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     */
    public function get(ApiRequest $request, $classroomId, $phoneNumber)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        if (empty($classroom)) {
            throw ClassroomException::NOTFOUND_CLASSROOM();
        }

        $user = $this->getUserService()->getUserByVerifiedMobile($phoneNumber);
        if (empty($user)) {
            return null;
        }

        $classroomMember = $this->getClassroomService()->getClassroomMember($classroomId, $user['id']);
        if (empty($classroomMember) || array('auditor') == $classroomMember['role']) {
            return null;
        }

        $this->getOCUtil()->single($classroomMember, array('userId'));

        return $classroomMember;
    }

    /**
     * @return MarketingClassroomService
     */
    protected function getMarketingClassroomService()
    {
        return $this->service('Marketing:MarketingClassroomService');
    }

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}
