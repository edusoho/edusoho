<?php

namespace Tests\Unit\Marketing\Tools;

use Biz\Marketing\Service\Impl\MarketingMemberServiceImpl;

class MockedMemberServiceImpl extends MarketingMemberServiceImpl
{
    public function becomeStudentAndCreateOrder($userId, $courseId, $data)
    {
        $this->userId = $userId;
        $this->courseId = $courseId;
        $this->data = $data;

        if ($data['price'] > 0) {
            $order = $this->createOrder($userId, $userId, $data);
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

    public function getCourseId()
    {
        return $this->courseId;
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
