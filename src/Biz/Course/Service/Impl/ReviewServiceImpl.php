<?php
namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Topxia\Common\ArrayToolkit;
use Biz\Course\Service\ReviewService;
use Codeages\Biz\Framework\Event\Event;

class ReviewServiceImpl extends BaseService implements ReviewService
{
    public function getReview($id)
    {
        return $this->getReviewDao()->get($id);
    }

    /**
     * [findCourseReviews description]
     *
     * @deprecated to be removed in 8.0. Use searchReviews() instead.
     *
     * @return array Course plan reviews
     */
    public function findCourseReviews($courseId, $start, $limit)
    {
        return $this->searchReviews(
            array('courseId' => $courseId),
            array('createdTime' => 'DESC'),
            $start,
            $limit
        );
    }

    /**
     * [findCourseReviews description]
     *
     * @deprecated to be removed in 8.0. Use searchReviewsCount() instead.
     *
     * @return integer
     */
    public function getCourseReviewCount($courseId)
    {
        return $this->searchReviewsCount(array('courseId' => $courseId));
    }

    public function getUserCourseReview($userId, $courseId)
    {
        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createNotFoundException('User is not Exist!');
        }

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException('Course is not Exist!');
        }

        return $this->getReviewDao()->getReviewByUserIdAndCourseId($userId, $courseId);
    }

    public function searchReviews($conditions, $sort, $start, $limit)
    {
        $orderBy = $this->checkOrderBy($sort);

        $conditions = $this->prepareReviewSearchConditions($conditions);
        return $this->getReviewDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchReviewsCount($conditions)
    {
        $conditions = $this->prepareReviewSearchConditions($conditions);
        return $this->getReviewDao()->count($conditions);
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

        if (!empty($conditions['author'])) {
            $author               = $this->getUserService()->getUserByNickname($conditions['author']);
            $conditions['userId'] = $author ? $author['id'] : -1;
        }

        if (!empty($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }

        return $conditions;
    }

    public function saveReview($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('courseId', 'rating'), true)) {
            throw $this->createInvalidArgumentException('invalid argument');
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($fields['courseId']);

        if (empty($course)) {
            throw $this->createNotFoundException("course(#{$fields['courseId']}) not found");
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $review = $this->getReviewDao()->getReviewByUserIdAndCourseId($user['id'], $course['id']);

        $fields['parentId'] = empty($fields['parentId']) ? 0 : $fields['parentId'];
        //$meta               = $fields['parentId'] > 0 ? array() : array('learnedNum' => $member['learnedNum'], 'lessonNum' => $course['lessonNum']);
        if (empty($review) || ($review && $fields['parentId'] > 0)) {
            $review = $this->getReviewDao()->create(array(
                'userId'      => $user['id'],
                'courseId'    => $fields['courseId'],
                'courseSetId' => $course['courseSetId'],
                'rating'      => $fields['rating'],
                'private'     => $course['status'] == 'published' ? 0 : 1,
                'parentId'    => $fields['parentId'],
                'content'     => empty($fields['content']) ? '' : $fields['content'],
                'createdTime' => time(),
                'meta'        => empty($fields['meta']) ? array() : $fields['meta']
            ));

            $this->dispatchEvent('course.review.add', new Event($review));
        } else {
            $review = $this->getReviewDao()->update($review['id'], array(
                'rating'      => $fields['rating'],
                'content'     => empty($fields['content']) ? '' : $fields['content'],
                'updatedTime' => time(),
                'meta'        => empty($fields['meta']) ? array() : $fields['meta']
            ));

            $this->dispatchEvent('course.review.update', new Event($review));
        }

        return $review;
    }

    public function deleteReview($id)
    {
        $review = $this->getReview($id);

        if (empty($review)) {
            throw $this->createNotFoundException("course review(#{$id}) not found");
        }

        $this->getCourseService()->tryManageCourse($review['courseId']);

        $result = $this->getReviewDao()->delete($id);

        $this->dispatchEvent('course.review.delete', new Event($review));

        $this->getLogService()->info('course', 'delete_review', "删除评价#{$id}");

        return $result;
    }

    /**
     * [countRatingByCourseId description]
     * @param  integer $courseId
     * @return array
     */
    public function countRatingByCourseId($courseId)
    {
        $conditions = array(
            'courseId' => $courseId,
            'parentId' => 0
        );
        $ratingNum = $this->searchReviewsCount($conditions);
        $rating    = $this->getReviewDao()->sumRatingByParams($conditions);

        return array(
            'ratingNum' => $ratingNum,
            'rating'    => $ratingNum ? $rating / $ratingNum : 0
        );
    }

    /**
     * [countRatingByCourseSetId description]
     * @param  integer $courseSetId
     * @return array
     */
    public function countRatingByCourseSetId($courseSetId)
    {
        $conditions = array(
            'courseSetId' => $courseSetId,
            'parentId'    => 0
        );
        $ratingNum = $this->searchReviewsCount($conditions);
        $rating    = $this->getReviewDao()->sumRatingByParams($conditions);

        return array(
            'ratingNum' => $ratingNum,
            'rating'    => $ratingNum ? $rating / $ratingNum : 0
        );
    }

    protected function checkOrderBy($sort)
    {
        if (is_array($sort)) {
            $orderBy = $sort;
        } elseif ($sort == 'latest') {
            $orderBy = array('createdTime' => 'DESC');
        } else {
            $orderBy = array('rating' => 'DESC');
        }

        return $orderBy;
    }

    protected function getReviewDao()
    {
        return $this->createDao('Course:ReviewDao');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
