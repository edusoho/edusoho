<?php

namespace AppBundle\Controller;

use AppBundle\Common\Paginator;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\User\Service\MessageService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Common\SimpleValidator;

class MemberOperationRecordController extends BaseController
{
    public function showExitRecordAction(request $request, $id, $type)
    {
        $function = 'tryManage'.ucfirst($type);
        if (!method_exists($this, $function)) {
            throw new \RuntimeException("{$function} not exsit");
        }

        $product = call_user_func(array($this, 'tryManage'.ucfirst($type)), $id);

        $condition = array(
            'targetId' => $id,
            'target_type' => $type,
            'status' => 'success',
            'operate_type' => 'exit',
        );

        $fields = $request->query->all();
        if (isset($fields['keyword']) && !empty($fields['keyword'])) {
            $condition['userIds'] = $this->getUserIds($fields['keyword']);
        }

        $paginator = new Paginator(
            $request,
            $this->getMemberOperationService()->countRecords($condition),
            20
        );

        $records = $this->getMemberOperationService()->searchRecords(
            $condition,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($records, 'user_id');
        $users = $this->getUserService()->findUsersByIds($userIds);
        
        $orderIds = ArrayToolkit::column($records, 'order_id');
        $orders = $this->getOrderService()->findOrdersByIds($orderIds);
        $orders = ArrayToolkit::index($orders, 'id');
        
        return $this->render(
            'member-record/quit.htm.twig',
            array(
                'product' => $product,
                'paginator' => $paginator,
                'records' => $records,
                'users' => $users,
                'orders' => $orders,
            )
        );
    }

    private function getUserIds($keyword)
    {
        if (SimpleValidator::email($keyword)) {
            $user = $this->getUserService()->getUserByEmail($keyword);

            return $user ? array($user['id']) : array(-1);
        }
        if (SimpleValidator::mobile($keyword)) {
            $mobileVerifiedUser = $this->getUserService()->getUserByVerifiedMobile($keyword);
            $profileUsers = $this->getUserService()->searchUserProfiles(
                array('tel' => $keyword),
                array('id' => 'DESC'),
                0,
                PHP_INT_MAX
            );
            $mobileNameUser = $this->getUserService()->getUserByNickname($keyword);
            $userIds = $profileUsers ? ArrayToolkit::column($profileUsers, 'id') : null;

            $userIds[] = $mobileVerifiedUser ? $mobileVerifiedUser['id'] : null;
            $userIds[] = $mobileNameUser ? $mobileNameUser['id'] : null;

            $userIds = array_unique($userIds);

            return $userIds ? $userIds : array(-1);
        }
        $user = $this->getUserService()->getUserByNickname($keyword);

        return $user ? array($user['id']) : array(-1);
    }

    private function tryManageClassroom($id)
    {
        $this->getClassroomService()->tryManageClassroom($id);

        return $this->getClassroomService()->getClassroom($id);
    }

    private function tryManageCourse($id)
    {
        return $this->getCourseService()->tryManageCourse($id);
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

   /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberOperationService
     */
    protected function getMemberOperationService()
    {
        return $this->createService('MemberOperation:MemberOperationService');
    }

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

}