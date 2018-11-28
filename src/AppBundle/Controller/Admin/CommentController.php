<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;

class CommentController extends BaseController
{
    public function indexAction(Request $request)
    {
        $comments = array();
        $total = $this->getCommentService()->getCommentsCountByType('course');
        $paginator = new Paginator($this->get('request'), $total, 30);
        $comments = $this->getCommentService()->getCommentsByType('course', $paginator->getOffsetCount(), $paginator->getPerPageCount());
        $userIds = ArrayToolkit::column($comments, 'userId');

        return $this->render('admin/comment/index.html.twig', array(
            'comments' => $comments,
            'userList' => $this->getUserService()->findUsersByIds($userIds),
            'paginator' => $paginator, ));
    }

    public function deleteAction(Request $request, $id)
    {
        $comment = $this->getCommentService()->getComment($id);
        if (empty($comment)) {
            return $this->createJsonResponse(array('status' => 'error', array('message' => 'Not Exsit!')));
        }
        $this->getCommentService()->deleteComment($id);

        return $this->createJsonResponse(array('status' => 'ok'));
    }

    protected function getCommentService()
    {
        return $this->createService('Content:CommentService');
    }
}
