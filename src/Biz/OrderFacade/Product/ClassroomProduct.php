<?php

namespace Biz\OrderFacade\Product;

use Biz\Accessor\AccessorInterface;
use Biz\Classroom\Service\ClassroomService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Order\Callback\PaidCallback;

class ClassroomProduct extends Product
{
    const TYPE = 'classroom';

    public $targetType = self::TYPE;

    public $showTemplate = 'order/show/classroom-item.html.twig';

    public function init(array $params)
    {
        $classroom = $this->getClassroomService()->getClassroom($params['targetId']);

        $this->targetId = $params['targetId'];
        $this->backUrl = array('routing' => 'classroom_show', 'params' => array('id' => $classroom['id']));
        $this->successUrl = array('my_course_show', array('id' => $this->targetId));
        $this->title = $classroom['title'];
        $this->price = $classroom['price'];
        $this->middlePicture = $classroom['middlePicture'];
    }

    public function validate()
    {
        $access = $this->getClassroomService()->canJoinClassroom($this->targetId);

        if ($access['code'] !== AccessorInterface::SUCCESS) {
            throw new InvalidArgumentException($access['msg']);
        }
    }

    public function paidCallback($orderItem)
    {
        $info = array(
            'orderId' => $orderItem['order_id'],
            'remark' => '',
        );

        $isStudent = $this->getClassroomService()->isClassroomStudent($orderItem['target_id'], $orderItem['user_id']);
        if (!$isStudent) {
            $this->getClassroomService()->becomeStudent($orderItem['target_id'], $orderItem['user_id'], $info);
        }

        return PaidCallback::SUCCESS;
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
