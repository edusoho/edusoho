<?php

namespace AppBundle\Controller;

use AppBundle\Common\ExportHelp;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\MemberOperation\Service\MemberOperationService;
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

        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);
        $profiles = ArrayToolkit::index($profiles, 'id');

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
                'profiles' => $profiles,
                'orders' => $orders,
                'conditions' => $condition,
            )
        );
    }

    public function exitRecordExportDatumAction(Request $request, $targetType, $targetId)
    {
        list($start, $limit, $exportAllowCount) = ExportHelp::getMagicExportSetting($request);

        list($title, $records, $memberCount) = $this->getExportData(
            $targetId,
            $targetType,
            $start,
            $limit,
            $exportAllowCount
        );

        $file = '';
        if (0 == $start) {
            $file = ExportHelp::addFileTitle($request, $targetType. '_students_exit_record', $title);
        }

        $content = implode("\r\n", $records);
        $file = ExportHelp::saveToTempFile($request, $content, $file);
        $status = ExportHelp::getNextMethod($start + $limit, $memberCount);

        return $this->createJsonResponse(
            [
                'status' => $status,
                'fileName' => $file,
                'start' => $start + $limit,
            ]
        );
    }

    public function exitRecordExportCsvAction(Request $request, $targetType, $targetId)
    {
        $fileName = sprintf($targetType . '-%s-%s-(%s).csv', $targetId, 'exit_record', date('Y-n-d'));

        return ExportHelp::exportCsv($request, $fileName);
    }

    private function getExportData($targetId, $targetType, $start, $limit, $exportAllowCount)
    {
        $function = 'tryManage'.ucfirst($targetType);
        if (!method_exists($this, $function)) {
            $this->createNewException(CommonException::NOTFOUND_METHOD());
        }

        call_user_func(array($this, 'tryManage'.ucfirst($targetType)), $targetId);

        $condition = array(
            'target_id' => $targetId,
            'target_type' => $targetType,
            'status' => 'success',
            'operate_type' => 'exit',
        );
        $recordCount = $this->getMemberOperationService()->countRecords($condition);

        $recordCount = ($recordCount > $exportAllowCount) ? $exportAllowCount : $recordCount;
        if ($recordCount < ($start + $limit + 1)) {
            $limit = $recordCount - $start;
        }

        $records = $this->getMemberOperationService()->searchRecords(
            $condition,
            ['created_time' => 'DESC'],
            $start,
            $limit
        );

        $userIds = ArrayToolkit::column($records, 'user_id');
        $operatorIds = ArrayToolkit::column($records, 'operator_id');
        $users = ArrayToolkit::index($this->getUserService()->findUsersByIds(array_merge($userIds, $operatorIds)), 'id');

        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);
        $profiles = ArrayToolkit::index($profiles, 'id');

        $orderIds = ArrayToolkit::column($records, 'order_id');
        $orders = $this->getOrderService()->findOrdersByIds($orderIds);
        $orders = ArrayToolkit::index($orders, 'id');

        $str = '学员名称,手机号,邮箱,退出日期,退出类型,退出原因,是否退款';

        $exitRecords = [];
        foreach ($records as $record) {
            $member = '';
            $member .= $users[$record['user_id']]['nickname']."\t".',';
            $member .= $profiles[$record['user_id']]['mobile'] ? $profiles[$record['user_id']]['mobile'].',' : '-'.',';
            $member .= $users[$record['user_id']]['email'].',';
            $member .= date('Y-n-d H:i:s', $record['operate_time']).',';
            $reasonType = $this->getReasonType($record['reason_type']);
            $member .= $reasonType ? $reasonType.',' : '-'.',';
            $reason = $this->getReason($users[$record['operator_id']], $users[$record['user_id']], $record);
            $member .= $reason ? $reason.',' : '-'.',';
            $isRefund = $this->getIsRefund($record, $orders[$record['order_id']]);
            $member .= $isRefund ? $isRefund.',' : '-'.',';

            $exitRecords[] = $member;
        }

        return [$str, $exitRecords, $recordCount];
    }

    protected function getReasonType($type)
    {
        $reasonType = '';
        switch ($type) {
            case 'remove':
                $reasonType = '移除';
                break;
            case 'exit':
                $reasonType = '主动退出';
                break;
            case 'refund':
                $reasonType = '退款成功';
                break;
        }

        return $reasonType;
    }

    protected function getReason($operator, $user, $record)
    {
        $exitReason = '';
        if ($user['id'] != $operator['id'] && $operator) {
            $exitReason .= '（'.$operator['nickname'].'）';
        }
        $exitReason .= $record['reason_type'] == 'remove' ? '手动移除' : $record['reason'];

        return $exitReason;
    }

    protected function getIsRefund($record, $order)
    {
        if ($record['refund_id'] > 0) {
            $isRefund = '是';
        } elseif ($order['pay_amount'] > 0) {
            $isRefund = '否';
        } else {
            $isRefund = '';
        }

        return $isRefund;
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
