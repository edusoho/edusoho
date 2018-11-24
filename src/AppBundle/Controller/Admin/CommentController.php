<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\AbstractException;

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
        try {
            $this->getCommentService()->deleteComment($id);

            return $this->createJsonResponse(array('status' => 'ok'));
        } catch (AbstractException $e) {
            return $this->createJsonResponse(array('status' => 'ok', 'error' => array($e->getMessage())));
        }
    }

    protected function getCommentService()
    {
        return $this->createService('Content:CommentService');
    }
}
