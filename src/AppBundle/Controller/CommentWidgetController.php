<?php

namespace AppBundle\Controller;

use Biz\Content\Service\CommentService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;

class CommentWidgetController extends BaseController
{
    public function initAction(Request $request)
    {
        $objectType = $request->query->get('objectType');
        $objectId = $request->query->get('objectId');

        $comment = array(
            'objectType' => $objectType,
            'objectId' => $objectId,
        );
        $comments = $this->getCommentService()->findComments($objectType, $objectId, 0, 1000);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($comments, 'userId'));

        return $this->render('comment-widget/init.html.twig', array(
            'comments' => $comments,
            'comment' => $comment,
            'users' => $users,
        ));
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $comment = $request->request->all();
            $comment = $this->getCommentService()->createComment($comment);

            return $this->render('comment-widget/item.html.twig', array(
                'comment' => $comment,
                'user' => $this->getCurrentUser(),
            ));
        }
    }

    public function deleteAction(Request $request)
    {
        $id = $request->query->get('id');
        $this->getCommentService()->deleteComment($id);

        return $this->createJsonResponse(true);
    }

    /**
     * @return CommentService
     */
    protected function getCommentService()
    {
        return $this->getBiz()->service('Content:CommentService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}
