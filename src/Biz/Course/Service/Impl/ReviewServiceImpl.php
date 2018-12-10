<?php

namespace Biz\Course\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Dao\ReviewDao;
use Biz\Course\ReviewException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\ReviewService;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Codeages\Biz\Framework\Event\Event;

class ReviewServiceImpl extends BaseService implements ReviewService
{
    public function getReview($id)
    {
        return $this->getReviewDao()->get($id);
    }

    /**
     * [findCourseReviews description].
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
     * [findCourseReviews description].
     *
     * @deprecated to be removed in 8.0. Use searchReviewsCount() instead.
     *
     * @return int
     */
    public function getCourseReviewCount($courseId)
    {
        return $this->searchReviewsCount(array('courseId' => $courseId));
    }

    public function getUserCourseReview($userId, $courseId)
    {
        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
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
            if (is_numeric($value)) {
                return true;
            }

            return !empty($value);
        }
        );

        if (!empty($conditions['author'])) {
            $author = $this->getUserService()->getUserByNickname($conditions['author']);
            $conditions['userId'] = $author ? $author['id'] : -1;
        }

        if (!empty($conditions['content'])) {
            $conditions['content'] = "{$conditions['content']}";
        }

        return $conditions;
    }

    public function saveReview($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('courseId', 'userId', 'rating'), true)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if ($fields['rating'] > 5) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($fields['courseId']);

        if (empty($course)) {
            $this->createNewException(CourseException::NOTFOUND_COURSE());
        }

        $user = $this->getUserService()->getUser($fields['userId']);

        if (empty($user)) {
            $this->createNewException(UserException::NOTFOUND_USER());
        }
        $taskCount = $this->getTaskService()->countTasks(array('courseId' => $course['id'], 'status' => 'published'));

        $review = $this->getReviewDao()->getReviewByUserIdAndCourseId($user['id'], $course['id']);

        $fields['parentId'] = empty($fields['parentId']) ? 0 : $fields['parentId'];

        $meta = $fields['parentId'] > 0 ? array() : array('learnedNum' => $member['learnedNum'], 'lessonNum' => $taskCount);

        if (!empty($fields['content'])) {
            $fields['content'] = $this->purifyHtml($fields['content']);
            $fields['content'] = $this->getSensitiveService()->sensitiveCheck($fields['content'], 'review');
        }

        if (empty($review) || ($review && $fields['parentId'] > 0)) {
            $review = $this->getReviewDao()->create(array(
                'userId' => $fields['userId'],
                'courseId' => $fields['courseId'],
                'courseSetId' => $course['courseSetId'],
                'rating' => $fields['rating'],
                'private' => 'published' == $course['status'] ? 0 : 1,
                'parentId' => $fields['parentId'],
                'content' => !isset($fields['content']) ? '' : $fields['content'],
                'createdTime' => time(),
                'meta' => $meta,
            ));
            $this->dispatchEvent('course.review.add', new Event($review, array('course' => $course)));
        } else {
            $review = $this->getReviewDao()->update($review['id'], array(
                'rating' => $fields['rating'],
                'content' => !isset($fields['content']) ? '' : $this->purifyHtml($fields['content']),
                'updatedTime' => time(),
                'meta' => $meta,
            ));

            $this->dispatchEvent('course.review.update', new Event($review));
        }

        return $review;
    }

    public function deleteReview($id)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $review = $this->getReview($id);

        if (empty($review)) {
            $this->createNewException(ReviewException::NOTFOUND_REVIEW());
        }

        if (!$user->isAdmin() && $review['userId'] != $user['id']) {
            $this->createNewException(UserException::PERMISSION_DENIED());
        }

        $this->getReviewDao()->delete($id);

        $this->dispatchEvent('course.review.delete', new Event($review));
    }

    /**
     * [countRatingByCourseId description].
     *
     * @param int $courseId
     *
     * @return array
     */
    public function countRatingByCourseId($courseId)
    {
        $conditions = array(
            'courseId' => $courseId,
            'parentId' => 0,
        );
        $ratingNum = $this->searchReviewsCount($conditions);
        $rating = $this->getReviewDao()->sumRatingByParams($conditions);

        return array(
            'ratingNum' => $ratingNum,
            'rating' => $ratingNum ? $rating / $ratingNum : 0,
        );
    }

    /**
     * [countRatingByCourseSetId description].
     *
     * @param int $courseSetId
     *
     * @return array
     */
    public function countRatingByCourseSetId($courseSetId)
    {
        $conditions = array(
            'courseSetId' => $courseSetId,
            'parentId' => 0,
        );
        $ratingNum = $this->searchReviewsCount($conditions);
        $rating = $this->getReviewDao()->sumRatingByParams($conditions);

        return array(
            'ratingNum' => $ratingNum,
            'rating' => $ratingNum ? $rating / $ratingNum : 0,
        );
    }

    protected function checkOrderBy($sort)
    {
        if (is_array($sort)) {
            $orderBy = $sort;
        } elseif ('latest' == $sort) {
            $orderBy = array('createdTime' => 'DESC');
        } else {
            $orderBy = array('rating' => 'DESC');
        }

        return $orderBy;
    }

    /**
     * @return ReviewDao
     */
    protected function getReviewDao()
    {
        return $this->createDao('Course:ReviewDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getSensitiveService()
    {
        return $this->createService('Sensitive:SensitiveService');
    }
}
