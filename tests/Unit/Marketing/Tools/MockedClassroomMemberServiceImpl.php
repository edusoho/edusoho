<?php

namespace Tests\Unit\Marketing\Tools;

use Biz\Marketing\Service\Impl\MarketingClassroomMemberServiceImpl;

class MockedClassroomMemberServiceImpl extends MarketingClassroomMemberServiceImpl
{
    public function becomeStudentWithOrder($classroomId, $userId, $data = array())
    {
        $this->userId = $userId;
        $this->classroomId = $classroomId;
        $this->data = $data;

        if ($data['price'] > 0) {
            $order = $this->createOrder($classroomId, $userId, $data);
            $this->order = $order;
        }

        return array(
            array(
                'id' => 12,
                'title' => 'course title',
            ),
            array(
                'id' => 12222,
            ),
            array(
                'id' => $order['id'],
            ),
        );
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getClassroomId()
    {
        return $this->classroomId;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getOrder()
    {
        return $this->order;
    }
}
