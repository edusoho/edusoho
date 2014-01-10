<?php
	namespace Topxia\AdminBundle\Controller;

	use Topxia\AdminBundle\Controller\BaseController;
	use Symfony\Component\HttpFoundation\Request;
	use Topxia\Common\ArrayToolkit;
	use Topxia\Common\Paginator;

	class UserlevelController extends BaseController 
	{

		public function indexAction (Request $request)
		{	
			$conditions = array_filter($request->query->all());
			$paginator = new Paginator(
	            $this->get('request'),
	            $this->getLevelService()->searchLevelsCount($conditions),
	            20
	        );
				$userlevels = $this->getLevelService()->searchLevels(
	            $conditions,
	            $paginator->getOffsetCount(),
	            $paginator->getPerPageCount()
	        );
				return $this->render('TopxiaAdminBundle:Userlevel:index.html.twig', array(
	            'userlevels' => $userlevels,
	            'paginator' => $paginator
	        ));
		}

		public function createAction (Request $request)
   		{   
	        if ('POST' == $request->getMethod()) {
			$userlevel = $this->getLevelService()->createLevel($request->request->all());
			return $this->render('TopxiaAdminBundle:Userlevel:tr.html.twig', array('userlevel' => $userlevel));
			}

			return $this->render('TopxiaAdminBundle:Userlevel:userlevel-modal.html.twig');
    	}

    	public function updateAction (Request $request,$id)
   		{   
   			$userlevel = $this->getLevelService()->getLevel($id);
			if (empty($userlevel)) {
				throw $this->createNotFoundException();
			}

	        if ('POST' == $request->getMethod()) {
			$userlevel = $this->getLevelService()->updateLevel($id, $request->request->all());
			return $this->render('TopxiaAdminBundle:Userlevel:tr.html.twig', array('userlevel' => $userlevel));
			}

	        return $this->render('TopxiaAdminBundle:Userlevel:userlevel-modal.html.twig', array(
			'userlevel' => $userlevel));
	    }

    	public function deleteAction(Request $request,$id)
		{
			$this->getLevelService()->deleteLevel($id);
			return $this->createJsonResponse(true);
		}

		public function checknameAction(Request $request)
	    {
	        $name = $request->query->get('value');
	        $exclude = $request->query->get('exclude');
	        $avaliable = $this->getLevelService()->isLevelNameAvailable($name, $exclude);
		       if ($avaliable) {
	            $response = array('success' => true, 'message' => '');
	        } else {
	            $response = array('success' => false, 'message' => '会员等级已存在');
	        }

	        return $this->createJsonResponse($response);
    	}

	    public function sortAction(Request $request)
	    {
	    	$this->getLevelService()->sortLevels($request->request->get('ids'));
			return $this->createJsonResponse(true);
	    }

		private function getLevel($id)
		{
			$level = $this->getLevelService()->getLevel($id);
			if (empty($level)) {
				throw $this->createNotFoundException('会员等级不存在!');
			}
			return $level;
		}

		protected function getLevelService()
    	{
        return $this->getServiceKernel()->createService('User.LevelService');
    	}
}