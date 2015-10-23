<?php
namespace Topxia\Service\Content;

interface CommentService
{
    CONST COMMENT_OBJECTTYPE_COURSE = 'course';
    CONST COMMENT_OBJECTTYPE_TEACHER = 'teacher';
    CONST COMMENT_OBJECTTYPE_QUESTION = 'question';

    public function createComment(array $comment);

    public function getComment($id);

    public function getCommentsCountByType($objectType);

    public function getCommentsByType($objectType, $start, $limit);

    public function findComments($objectType, $objectId, $start, $limit);

    public function deleteComment($id);
}