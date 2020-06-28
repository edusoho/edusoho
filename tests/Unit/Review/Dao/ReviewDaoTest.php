<?php

namespace Tests\Unit\Review\Dao;

use Biz\BaseTestCase;
use Biz\Review\Dao\ReviewDao;

class ReviewDaoTest extends BaseTestCase
{
    public function testGetByUserIdAndTargetTypeAndTargetId()
    {
        $expected = $this->createReview();
        $resultNull = $this->getReviewDao()->getByUserIdAndTargetTypeAndTargetId($expected['userId'] + 10000, $expected['targetType'], $expected['targetId']);

        $this->assertNull($resultNull);

        $result = $this->getReviewDao()->getByUserIdAndTargetTypeAndTargetId($expected['userId'], $expected['targetType'], $expected['targetId']);
        $this->assertEquals($expected, $result);
    }

    public function testSumRatingByConditions()
    {
        $review1 = $this->createReview();
        $review2 = $this->createReview(['rating' => 1, 'targetId' => $review1['targetId'] + 1000]);
        $review3 = $this->createReview(['rating' => 3]);

        $result = $this->getReviewDao()->sumRatingByConditions(['targetId' => $review1['targetId']]);
        $this->assertEquals($review1['rating'] + $review3['rating'], $result);
    }

    public function testDeleteByParentId()
    {
        $review = $this->createReview();
        $review1 = $this->createReview(['parentId' => $review['id']]);

        $before = $this->getReviewDao()->get($review1['id']);

        $this->getReviewDao()->deleteByParentId($review1['parentId']);

        $after = $this->getReviewDao()->get($review1['id']);
        $this->assertEquals($review1, $before);
        $this->assertNull($after);
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
