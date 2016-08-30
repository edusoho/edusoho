<?php

namespace Classroom\Service\Classroom\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceEvent;
use Classroom\Service\Classroom\ClassroomReviewService;

class ClassroomReviewServiceImpl extends BaseService implements ClassroomReviewService
{
    public function getReview($id)
    {
        return $this->getClassroomReviewDao()->getReview($id);
    }

    public function searchReviews($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareReviewSearchConditions($conditions);

        return $this->getClassroomReviewDao()->searchReviews($conditions, $orderBy, $start, $limit);
    }

    public function searchReviewCount($conditions)
    {
        $conditions = $this->_prepareReviewSearchConditions($conditions);
        $count      = $this->getClassroomReviewDao()->searchReviewCount($conditions);

        return $count;
    }

    public function getUserClassroomReview($userId, $classroomId)
    {
        $user = $this->getUserService()->getUser($userId);

        $classroom = $this->getClassroomDao()->getClassroom($classroomId);

        if (empty($classroom)) {
            throw $this->createServiceException("Classroom is not Exist!");
        }

        return $this->getClassroomReviewDao()->getReviewByUserIdAndClassroomId($userId, $classroomId);
    }

    private function _prepareReviewSearchConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if (is_array($value) || ctype_digit((string) $value)) {
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
        if (!ArrayToolkit::requireds($fields, array('classroomId', 'userId', 'rating'), true)) {
            throw $this->createServiceException('参数不正确，评价失败！');
        }

        $classroom = $this->getClassroomDao()->getClassroom($fields['classroomId']);

        $userId = $this->getCurrentUser()->id;

        if (empty($classroom)) {
            throw $this->createServiceException("班级(#{$fields['classroomId']})不存在，评价失败！");
        }

        $user = $this->getUserService()->getUser($fields['userId']);

        if (empty($user)) {
            throw $this->createServiceException("用户(#{$fields['userId']})不存在,评价失败!");
        }

        $review = $this->getClassroomReviewDao()->getReviewByUserIdAndClassroomId($user['id'], $classroom['id']);

        if (empty($review)) {
            $review = $this->getClassroomReviewDao()->addReview(array(
                'userId'      => $fields['userId'],
                'classroomId' => $fields['classroomId'],
                'rating'      => $fields['rating'],
                'content'     => empty($fields['content']) ? '' : $fields['content'],
                'title'       => empty($fields['title']) ? '' : $fields['title'],
                'createdTime' => time()
            ));
            $this->dispatchEvent('classReview.add', new ServiceEvent($review));
        } else {
            $review = $this->getClassroomReviewDao()->updateReview($review['id'], array(
                'rating'  => $fields['rating'],
                'title'   => empty($fields['title']) ? '' : $fields['title'],
                'content' => empty($fields['content']) ? '' : $fields['content']
            ));
        }

        $this->calculateClassroomRating($classroom['id']);

        return $review;
    }

    private function calculateClassroomRating($classroomId)
    {
        $ratingSum = $this->getClassroomReviewDao()->getReviewRatingSumByClassroomId($classroomId);
        $ratingNum = $this->getClassroomReviewDao()->getReviewCountByClassroomId($classroomId);

        $this->getClassroomService()->updateClassroom($classroomId, array(
            'rating'    => $ratingNum ? $ratingSum / $ratingNum : 0,
            'ratingNum' => $ratingNum
        ));
    }

    public function deleteReview($id)
    {
        $review = $this->getReview($id);

        if (empty($review)) {
            throw $this->createServiceException("评价(#{$id})不存在，删除失败！");
        }

        $this->getClassroomReviewDao()->deleteReview($id);

        $this->calculateClassroomRating($review['classroomId']);

        $this->getLogService()->info('classroom', 'delete_review', "删除评价#{$id}");
    }

    protected function getClassroomReviewDao()
    {
        return $this->createDao('Classroom:Classroom.ClassroomReviewDao');
    }

    private function getClassroomService()
    {
        return $this->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getClassroomDao()
    {
        return $this->createDao('Classroom:Classroom.ClassroomDao');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }
}
