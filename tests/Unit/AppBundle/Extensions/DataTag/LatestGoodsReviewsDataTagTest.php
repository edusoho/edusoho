<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Extensions\DataTag\LatestGoodsReviewsDataTag;
use Biz\BaseTestCase;

class LatestGoodsReviewsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $user1 = $this->getUserService()->register([
            'email' => '1234@qq.com',
            'nickname' => 'user1',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
        ]);

        $user2 = $this->getUserService()->register([
            'email' => '12345@qq.com',
            'nickname' => 'user2',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
        ]);

        $review1 = $this->getReviewDao()->create([
            'targetType' => 'goods',
            'targetId' => 1,
            'userId' => $user1['id'],
            'content' => 'content1',
            'rating' => 4,
            'auditStatus' => 'pass',
        ]);
        $review2 = $this->getReviewDao()->create([
            'targetType' => 'goods',
            'targetId' => 1,
            'userId' => $user2['id'],
            'content' => 'content2',
            'rating' => 4,
            'auditStatus' => 'pass',
        ]);

        $dataTag = new LatestGoodsReviewsDataTag();
        $reviews = ArrayToolkit::index($dataTag->getData(['count' => 4]), 'id');
        self::assertCount(2, $reviews);
        self::assertEquals($review1['targetId'], $reviews[$review1['id']]['targetId']);
        self::assertEquals($review2['targetId'], $reviews[$review2['id']]['targetId']);
    }

    private function getReviewDao()
    {
        return $this->createDao('Review:ReviewDao');
    }

    public function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
