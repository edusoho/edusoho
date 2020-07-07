<?php

namespace Biz\Review\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Review\Dao\ReviewDao;
use Biz\Review\ReviewException;
use Biz\Review\Service\ReviewService;
use Biz\Sensitive\Service\SensitiveService;
use Codeages\Biz\Framework\Event\Event;

class ReviewServiceImpl extends BaseService implements ReviewService
{
    const RATING_LIMIT = 5;

//    TODO: 剥离完课程与班级数据后删除
    protected $reviewMap = [
        'course' => 'tryCreateCourseReview',
        'classroom' => 'tryCreateClassroomReview',
    ];

    public function getReview($id)
    {
        return $this->getReviewDao()->get($id);
    }

    //    TODO: 暂时兼容课程、班级,权限判断修改
    public function tryCreateReview($review)
    {
        if (!in_array($review['targetType'], array_keys($this->reviewMap))) {
            return $review;
        }

        $function = $this->reviewMap[$review['targetType']];

        return $this->$function($review);
    }

    public function createReview($review)
    {
        if (!ArrayToolkit::requireds($review, ['userId', 'rating', 'targetType', 'targetId', 'content'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if ($review['rating'] > self::RATING_LIMIT) {
            $this->createNewException(ReviewException::RATING_LIMIT());
        }

        $review = $this->tryCreateReview($review);

        $review = ArrayToolkit::parts($review, [
            'userId', 'targetType', 'targetId', 'content', 'rating', 'parentId',
        ]);

        $review['content'] = $this->purifyHtml($review['content']);
        $review['content'] = $this->getSensitiveService()->sensitiveCheck($review['content'], 'review');

        $review = $this->getReviewDao()->create($review);
        $this->dispatchEvent('review.create', new Event($review));

        return $review;
    }

    public function getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId)
    {
        return $this->getReviewDao()->getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);
    }

    public function updateReview($id, $review)
    {
        $existed = $this->getReviewDao()->get($id);
        $this->tryOperateReview($existed);
        $review = ArrayToolkit::parts($review, ['content', 'rating']);

        $review['content'] = $this->purifyHtml($review['content']);
        $review['content'] = $this->getSensitiveService()->sensitiveCheck($review['content'], 'review');

        $review = $this->getReviewDao()->update($id, $review);

        $this->dispatchEvent('review.update', new Event($review));

        return $review;
    }

    public function deleteReview($id)
    {
        $review = $this->getReviewDao()->get($id);

        if (empty($review)) {
            return true;
        }

        $this->tryOperateReview($review);
        $this->getReviewDao()->delete($id);

        $this->getReviewDao()->deleteByParentId($review['id']);

        $this->dispatchEvent('review.delete', new Event($review));

        return true;
    }

    public function countReviews($conditions)
    {
        return $this->getReviewDao()->count($conditions);
    }

    public function searchReviews($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getReviewDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countRatingByTargetTypeAndTargetId($targetType, $targetId)
    {
        $conditions = [
            'targetType' => $targetType,
            'targetId' => $targetId,
            'parentId' => 0,
        ];
        $ratingNum = $this->countReviews($conditions);
        $rating = $this->getReviewDao()->sumRatingByConditions($conditions);

        return [
            'ratingNum' => $ratingNum,
            'rating' => $ratingNum ? $rating / $ratingNum : 0,
        ];
    }

    public function countRatingByTargetTypeAndTargetIds($targetType, $targetIds)
    {
        $conditions = [
            'targetType' => $targetType,
            'targetIds' => $targetIds,
            'parentId' => 0,
        ];
        $ratingNum = $this->countReviews($conditions);
        $rating = $this->getReviewDao()->sumRatingByConditions($conditions);

        return [
            'ratingNum' => $ratingNum,
            'rating' => $ratingNum ? $rating / $ratingNum : 0,
        ];
    }

    protected function tryOperateReview($review)
    {
        if ($review['userId'] != $this->getCurrentUser()->getId() && !$this->getCurrentUser()->isAdmin()) {
            $this->createNewException(ReviewException::FORBIDDEN_OPERATE_REVIEW());
        }
    }

    //    TODO: 商品剥离暂时兼容课程
    protected function tryCreateCourseReview($review)
    {
        if (!$this->getCourseService()->canTakeCourse($review['targetId'])) {
            $this->createNewException(ReviewException::FORBIDDEN_CREATE_REVIEW());
        }

        return $review;
    }

//    TODO: 商品剥离暂时兼容班级
    protected function tryCreateClassroomReview($review)
    {
        if (!$this->getClassroomService()->canTakeClassroom($review['targetId'])) {
            $this->createNewException(ReviewException::FORBIDDEN_CREATE_REVIEW());
        }

        return $review;
    }

    /**
     * @return ReviewDao
     */
    protected function getReviewDao()
    {
        return $this->createDao('Review:ReviewDao');
    }

    /**
     * @return SensitiveService
     */
    protected function getSensitiveService()
    {
        return $this->createService('Sensitive:SensitiveService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
