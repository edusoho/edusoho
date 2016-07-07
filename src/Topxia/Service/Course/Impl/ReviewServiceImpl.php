<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Course\ReviewService;

class ReviewServiceImpl extends BaseService implements ReviewService
{
    public function getReview($id)
    {
        return $this->getReviewDao()->getReview($id);
    }

    public function findCourseReviews($courseId, $start, $limit)
    {
        return $this->getReviewDao()->findReviewsByCourseId($courseId, $start, $limit);
    }

    public function getCourseReviewCount($courseId)
    {
        return $this->getReviewDao()->getReviewCountByCourseId($courseId);
    }

    public function getUserCourseReview($userId, $courseId)
    {
        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createServiceException("User is not Exist!");
        }

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException("Course is not Exist!");
        }

        return $this->getReviewDao()->getReviewByUserIdAndCourseId($userId, $courseId);
    }

    public function searchReviews($conditions, $sort, $start, $limit)
    {
        if ($sort == 'latest') {
            $orderBy = array('createdTime', 'DESC');
        } else {
            $orderBy = array('rating', 'DESC');
        }

        $conditions = $this->prepareReviewSearchConditions($conditions);
        return $this->getReviewDao()->searchReviews($conditions, $orderBy, $start, $limit);
    }

    public function searchReviewsCount($conditions)
    {
        $conditions = $this->prepareReviewSearchConditions($conditions);
        return $this->getReviewDao()->searchReviewsCount($conditions);
    }

    protected function prepareReviewSearchConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if (ctype_digit((string) $value)) {
                return true;
            }

            return !empty($value);
        }

        );

        if (isset($conditions['author'])) {
            $author               = $this->getUserService()->getUserByNickname($conditions['author']);
            $conditions['userId'] = $author ? $author['id'] : -1;
        }

        return $conditions;
    }

    public function saveReview($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('courseId', 'userId', 'rating'), true)) {
            throw $this->createServiceException('参数不正确，评价失败！');
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($fields['courseId']);

        $userId = $this->getCurrentUser()->id;

        if (empty($course)) {
            throw $this->createServiceException("课程(#{$fields['courseId']})不存在，评价失败！");
        }

        $user = $this->getUserService()->getUser($fields['userId']);

        if (empty($user)) {
            throw $this->createServiceException("用户(#{$fields['userId']})不存在,评价失败!");
        }

        $review = $this->getReviewDao()->getReviewByUserIdAndCourseId($user['id'], $course['id']);

        if (empty($review)) {
            $review = $this->getReviewDao()->addReview(array(
                'userId'      => $fields['userId'],
                'courseId'    => $fields['courseId'],
                'rating'      => $fields['rating'],
                'private'     => $course['status'] == 'published' ? 0 : 1,
                'content'     => empty($fields['content']) ? '' : $fields['content'],
                'createdTime' => time()
            ));
            $this->dispatchEvent('courseReview.add', new ServiceEvent($review));
        } else {
            $review = $this->getReviewDao()->updateReview($review['id'], array(
                'rating'  => $fields['rating'],
                'content' => empty($fields['content']) ? '' : $fields['content']
            ));
        }

        $this->calculateCourseRating($course['id']);

        return $review;
    }

    public function deleteReview($id)
    {
        $review = $this->getReview($id);

        if (empty($review)) {
            throw $this->createServiceException("评价(#{$id})不存在，删除失败！");
        }

        $this->getReviewDao()->deleteReview($id);

        $this->calculateCourseRating($review['courseId']);

        $this->getLogService()->info('course', 'delete_review', "删除评价#{$id}");
    }

    protected function calculateCourseRating($courseId)
    {
        $ratingSum = $this->getReviewDao()->getReviewRatingSumByCourseId($courseId);
        $ratingNum = $this->getReviewDao()->getReviewCountByCourseId($courseId);

        $this->getCourseService()->updateCourseCounter($courseId, array(
            'rating'    => $ratingNum ? $ratingSum / $ratingNum : 0,
            'ratingNum' => $ratingNum
        ));
    }

    protected function isCurrentUser($userId)
    {
        $user = $this->getCurrentUser();

        if ($userId == $user->id) {
            return true;
        }

        return false;
    }

    protected function getReviewDao()
    {
        return $this->createDao('Course.ReviewDao');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }
}
