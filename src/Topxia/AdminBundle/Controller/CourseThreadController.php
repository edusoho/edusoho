<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class CourseThreadController extends BaseController
{

    public function indexAction (Request $request)
    {

    	$form = $this->createFormBuilder()
    		->add('keywords', 'text', array('required' => false))
    		->add('nickname', 'text', array('required' => false))
			->getForm();

		$form->bind($request);

		$conditions = $form->getData();

		$convertedConditions = $this->convertConditions($conditions);

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($convertedConditions),
            20
        );
        
        $threads = $this->getThreadService()->searchThreads(
            $convertedConditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'userId'));
    	return $this->render('TopxiaAdminBundle:CourseThread:index.html.twig', array(
    		'form' => $form->createView(),
    		'paginator' => $paginator,
            'threads' => $threads,
            'users'=>$users
		));
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getThreadService()->deleteThread($id);
        return $this->createJsonResponse(true);
    }

    public function deleteChoosedThreadsAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids as $id) {
            $this->getThreadService()->deleteThread($id);
        }
        return $this->createJsonResponse(true);
    }

	private function convertConditions($conditions)
	{
		if (!empty($conditions['nickname'])) {
			$user = $this->getUserService()->getUserByNickname($conditions['nickname']);
			if (empty($user)) {
				throw $this->createNotFoundException(sprintf("昵称为%s的用户不存在", $conditions['nickname']));
			}

			$conditions['userId'] = $user['id'];
		}
		unset($conditions['nickname']);

		if (empty($conditions['keywords'])) {
			unset($conditions['keywords']);
		}

		return $conditions;
	}

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

}