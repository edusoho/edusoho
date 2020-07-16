<?php


namespace AppBundle\Controller\ItemBankExercise;


use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\ItemBankExercise\Service\MemberOperationRecordService;
use Codeages\Biz\Order\Service\OrderService;
use Symfony\Component\HttpFoundation\Request;

class MemberOperationRecordController extends BaseController
{
    public function showRecordAction(Request $request, $operatType, $exerciseId)
    {
        $exercise = $this->getExerciseService()->tryManageExercise($exerciseId);

        $condition = array(
            'exerciseId' => $exercise['id'],
            'status' => 'success',
            'operateType' => $operatType,
        );

        $fields = $request->query->all();
        if (isset($fields['keyword']) && !empty($fields['keyword'])) {
            $condition['userIds'] = $this->getUserService()->getUserIdsByKeyword($fields['keyword']);
        }

        $paginator = new Paginator(
            $request,
            $this->getMemberOperationService()->count($condition),
            20
        );

        $records = $this->getMemberOperationService()->search(
            $condition,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($records, 'userId');
        $operaterIds = ArrayToolkit::column($records, 'operatorId');
        $users = $this->getUserService()->findUsersByIds(array_merge($userIds, $operaterIds));

        $orderIds = ArrayToolkit::column($records, 'orderId');
        $orders = $this->getOrderService()->findOrdersByIds($orderIds);
        $orders = ArrayToolkit::index($orders, 'id');

        $condition = $request->query->all();

        return $this->render(
            "item-bank-exercise-manage/member-record/{$operatType}.html.twig",
            array(
                'paginator' => $paginator,
                'records' => $records,
                'users' => $users,
                'orders' => $orders,
                'conditions' => $condition,
            )
        );
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return MemberOperationRecordService
     */
    protected function getMemberOperationService()
    {
        return $this->createService('ItemBankExercise:MemberOperationRecordService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}