<?php

namespace Biz\Content\Service;

interface CommentService
{
    const COMMENT_OBJECTTYPE_COURSE = 'course';
    const COMMENT_OBJECTTYPE_TEACHER = 'teacher';
    const COMMENT_OBJECTTYPE_QUESTION = 'question';

    public function createComment(array $comment);

    public function getComment($id);

    public function getCommentsCountByType($objectType);

    public function getCommentsByType($objectType, $start, $limit);

    public function findComments($objectType, $objectId, $start, $limit);

    public function deleteComment($id);
}
