<?php

namespace AppBundle\Controller;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Symfony\Component\HttpFoundation\Request;

class MemberOperationRecordController extends BaseController
{
    public function showRecordAction(request $request, $operatType, $targetId, $targetType)
    {
        $function = 'tryManage'.ucfirst($targetType);
        if (!method_exists($this, $function)) {
            $this->createNewException(CommonException::NOTFOUND_METHOD());
        }

        $product = call_user_func(array($this, 'tryManage'.ucfirst($targetType)), $targetId);

        $condition = array(
            'target_id' => $targetId,
            'target_type' => $targetType,
            'status' => 'success',
            'operate_type' => $operatType,
        );

        $fields = $request->query->all();
        if (isset($fields['keyword']) && !empty($fields['keyword'])) {
            $condition['user_ids'] = $this->getUserService()->getUserIdsByKeyword($fields['keyword']);
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
        $operaterIds = ArrayToolkit::column($records, 'operator_id');
        $users = $this->getUserService()->findUsersByIds(array_merge($userIds, $operaterIds));

        $orderIds = ArrayToolkit::column($records, 'order_id');
        $orders = $this->getOrderService()->findOrdersByIds($orderIds);
        $orders = ArrayToolkit::index($orders, 'id');

        $condition = $request->query->all();

        return $this->render(
            "member-record/{$operatType}.html.twig",
            array(
                'product' => $product,
                'paginator' => $paginator,
                'records' => $records,
                'users' => $users,
                'orders' => $orders,
                'conditions' => $condition,
            )
        );
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
