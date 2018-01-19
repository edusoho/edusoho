<?php

namespace Biz\Content\Service\Impl;

use Biz\BaseService;
use Biz\Content\Dao\CommentDao;
use Biz\Content\Service\CommentService;
use Biz\Course\Service\CourseService;
use AppBundle\Common\TimeMachine;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class CommentServiceImpl extends BaseService implements CommentService
{
    public function createComment(array $comment)
    {
        try {
            $this->checkCommentObjectFields($comment['objectType']);
        } catch (InvalidArgumentException $exception) {
            throw $exception;
        }

        try {
            $this->checkCommentObjectValue($comment);
        } catch (NotFoundException $exception) {
            throw $exception;
        }

        $fields = array();
        $fields['objectType'] = $comment['objectType'];
        $fields['objectId'] = $comment['objectId'];
        $fields['content'] = $comment['content'];
        $fields['userId'] = $this->getCurrentUser()->id;
        $fields['createdTime'] = TimeMachine::time();

        return $this->getCommentDao()->create($fields);
    }

    public function getComment($id)
    {
        return $this->getCommentDao()->get($id);
    }

    public function findComments($objectType, $objectId, $start, $limit)
    {
        $this->checkCommentObjectFields($objectType);
        $this->checkCommentObjectValue(array('objectType' => $objectType, 'objectId' => $objectId));

        return $this->getCommentDao()->findByObjectTypeAndObjectId($objectType, $objectId, $start, $limit);
    }

    public function deleteComment($id)
    {
        $user = $this->getCurrentUser();
        $comment = $this->getComment($id);

        if (empty($comment)) {
            throw $this->createNotFoundException('评论不存在');
        }

        if (empty($user)) {
            throw $this->createAccessDeniedException('无权限删除评论！');
        }

        if ($comment['userId'] != $user['id'] && !$user->isAdmin()) {
            throw $this->createAccessDeniedException('无权限删除评论！');
        }

        return $this->getCommentDao()->delete($id);
    }

    public function getCommentsByType($objectType, $start, $limit)
    {
        $this->checkCommentObjectFields($objectType);

        return $this->getCommentDao()->findByObjectType($objectType, $start, $limit);
    }

    public function getCommentsCountByType($objectType)
    {
        $this->checkCommentObjectFields($objectType);

        return $this->getCommentDao()->countByObjectType($objectType);
    }

    protected function checkCommentObjectFields($objectType)
    {
        $objectTypes = array('course');
        if (!in_array($objectType, $objectTypes)) {
            throw $this->createInvalidArgumentException('不存在当前这种评论对象');
        }
    }

    //TODO 对于多种对象的评论应该实现检测评论的对象是否存在
    protected function checkCommentObjectValue($comment)
    {
        switch ($comment['objectType']) {
            case self::COMMENT_OBJECTTYPE_COURSE:
                $foundCourse = $this->getCourseService()->getCourse($comment['objectId']);
                if (empty($foundCourse)) {
                    throw $this->createNotFoundException('评论教学计划失败，该教学计划不存在');
                }
                break;

            default:
                break;
        }
    }

    /**
     * @return CommentDao
     */
    protected function getCommentDao()
    {
        return $this->createDao('Content:CommentDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
