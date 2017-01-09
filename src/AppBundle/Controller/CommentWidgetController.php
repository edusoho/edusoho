<?php
namespace AppBundle\Controller;

use Biz\Content\Service\CommentService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Form\CommentType;

class CommentWidgetController extends BaseController
{
	public function initAction(Request $request)
	{
		$objectType = $request->query->get('objectType');
		$objectId = $request->query->get('objectId');

		$form = $this->createForm(new CommentType(), array(
			'objectType' => $objectType,
			'objectId' => $objectId,
		));

		$comments = $this->getCommentService()->findComments($objectType, $objectId, 0, 1000);
		$users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($comments, 'userId'));
		return $this->render('comment-widget/init.html.twig', array(
			'form' => $form->createView(),
			'comments' => $comments,
			'users' => $users,
		));
	}

	public function createAction(Request $request)
	{
		$form = $this->createForm(new CommentType());
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $comment = $form->getData();
                $comment = $this->getCommentService()->createComment($comment);
				return $this->render('comment-widget/item.html.twig', array(
					'comment' => $comment,
					'user' => $this->getCurrentUser(),
				));
            }
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