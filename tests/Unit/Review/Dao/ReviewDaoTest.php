<?php

namespace Tests\Unit\Review\Dao;

use Biz\BaseTestCase;
use Biz\Review\Dao\ReviewDao;

class ReviewDaoTest extends BaseTestCase
{
    public function testGetByUserIdAndTargetIdAndTargetType()
    {
        $expected = $this->createReview();
        $resultNull = $this->getReviewDao()->getByUserIdAndTargetIdAndTargetType($expected['userId'] + 10000, $expected['targetType'], $expected['targetId']);

        $this->assertNull($resultNull);

        $result = $this->getReviewDao()->getByUserIdAndTargetIdAndTargetType($expected['userId'], $expected['targetType'], $expected['targetId']);
        $this->assertEquals($expected, $result);
    }

    protected function createReview($fields = [])
    {
        $review = array_merge([
            'userId' => $this->getCurrentUser()->getId(),
            'targetType' => 'goods',
            'targetId' => 1,
            'rating' => 5,
            'content' => 'test content',
            'parentId' => 0,
        ], $fields);

        return $this->getReviewDao()->create($review);
    }

    /**
     * @return ReviewDao
     */
    protected function getReviewDao()
    {
        return $this->createDao('Review:ReviewDao');
    }
}
