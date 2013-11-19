<?php
	namespace Topxia\AdminBundle\Controller;

	use Topxia\AdminBundle\Controller\BaseController;
	use Symfony\Component\HttpFoundation\Request;
	use Topxia\Common\ArrayToolkit;
	use Topxia\Common\Paginator;

	class UserlevelController extends BaseController {

		public function indexAction (Request $request)
		{	
			$conditions = array_filter($request->query->all());

			$paginator = new Paginator(
	            $this->get('request'),
	            $this->getUserService()->searchUserlevelsCount($conditions),
	            20
	        );
				$userlevels = $this->getUserService()->searchUserlevels(
	            $conditions,
	            $paginator->getOffsetCount(),
	            $paginator->getPerPageCount()
	        );
				return $this->render('TopxiaAdminBundle:Userlevel:index.html.twig', array(
	            'userlevels' => $userlevels ,
	            'paginator' => $paginator
	        ));
		}

		public function createAction (Request $request)
   		{   
	        if ('POST' == $request->getMethod()) {
			$userlevel = $this->getUserService()->createUserlevel($request->request->all());
			return $this->render('TopxiaAdminBundle:Userlevel:tr.html.twig', array('userlevel' => $userlevel));
			}

			return $this->render('TopxiaAdminBundle:Userlevel:userlevel-modal.html.twig');
    	}

    	public function updateAction (Request $request,$id)
   		{   
   			$userlevel = $this->getUserService()->getUserlevel($id);
			if (empty($userlevel)) {
				throw $this->createNotFoundException();
			}

	        if ('POST' == $request->getMethod()) {
			$userlevel = $this->getUserService()->updateUserlevel($id, $request->request->all());
			return $this->render('TopxiaAdminBundle:Userlevel:tr.html.twig', array('userlevel' => $userlevel));
			}

	        return $this->render('TopxiaAdminBundle:Userlevel:userlevel-modal.html.twig', array(
			'userlevel' => $userlevel
			));
	    }

    	public function deleteAction(Request $request,$id)
		{
			$this->getUserService()->deleteUserlevel($id);
			return $this->createJsonResponse(true);
		}

		public function checknameAction(Request $request)
	    {
	        $name = $request->query->get('value');
	        $exclude = $request->query->get('exclude');
	        $avaliable = $this->getUserService()->isUserlevelNameAvailable($name, $exclude);
		       if ($avaliable) {
	            $response = array('success' => true, 'message' => '');
	        } else {
	            $response = array('success' => false, 'message' => '会员等级已存在');
	        }

	        return $this->createJsonResponse($response);
    	}

	    public function sortAction(Request $request)
	    {
	    	$this->getUserService()->sortUserlevels($request->request->get('ids'));
			return $this->createJsonResponse(true);
	    }

		private function getUserlevel($id)
		{
			$userlevel = $this->getUserService()->getUserlevel($id);
			if (empty($userlevel)) {
				throw $this->createNotFoundException('会员等级不存在!');
			}
			return $userlevel;
		}
}
?>