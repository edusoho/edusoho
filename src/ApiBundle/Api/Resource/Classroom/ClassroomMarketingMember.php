<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use Biz\Marketing\Service\MarketingService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ApiBundle\Api\Annotation\ResponseFilter;
use Symfony\Component\Filesystem\Filesystem;
use Topxia\Service\Common\ServiceKernel;

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
        $fileSystem = new Filesystem();
        $path = ServiceKernel::instance()->getParameter('kernel.root_dir').'/../web/files/test/test.txt';
        if (!file_exists(dirname($path))) {
            $fileSystem->mkdir(dirname($path));
        }
        file_put_contents($path, $request->headers, FILE_APPEND);
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        if (empty($classroom)) {
            throw new NotFoundHttpException('班级不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }
        $biz = $this->getBiz();
        $logger = $biz['logger'];
        $logger->debug('微营销通知处理订单');
        $postData = $request->request->all();

        try {
            return $this->getMarketingService()->addUserToClassroom($postData);
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
            throw new NotFoundHttpException('班级不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        $user = $this->getUserService()->getUserByVerifiedMobile($phoneNumber);
        if (empty($user)) {
            return null;
        }

        $classroomMember = $this->getClassroomService()->getClassroomMember($classroomId, $user['id']);
        $this->getOCUtil()->single($classroomMember, array('userId'));

        return $classroomMember;
    }

    /**
     * @return MarketingService
     */
    protected function getMarketingService()
    {
        return $this->service('Marketing:MarketingService');
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
