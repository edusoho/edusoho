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
	        	$conditions = $request->request->all();
	        	if(@$conditions['monthType']){
	        		unset($conditions['monthType']);
	        	} else { unset($conditions['monthPrice']); }
	        	if(@$conditions['yearType']){
	        		unset($conditions['yearType']);
	        	} else { unset($conditions['yearPrice']); }

				$userlevel = $this->getLevelService()->createLevel($conditions);
				if($userlevel){
					$this->setFlashMessage('success', '会员类型已保存！');
				}
				return $this->redirect($this->generateUrl('admin_user_level'));
			}

			return $this->render('TopxiaAdminBundle:Userlevel:userlevel.html.twig');
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

		public function pictureAction(Request $request)
		{
			return $this->render('TopxiaAdminBundle:Userlevel:userlevel-modal.html.twig');
		}

		public function checknameAction(Request $request)
	    {
	        $name = $request->query->get('value');
	        $exclude = $request->query->get('exclude');
	        $avaliable = $this->getLevelService()->isLevelNameAvailable($name, $exclude);
		       if ($avaliable) {
	            $response = array('success' => true, 'message' => '');
	        } else {
	            $response = array('success' => false, 'message' => '会员类型已存在');
	        }

	        return $this->createJsonResponse($response);
    	}

	    public function sortAction(Request $request)
	    {
	    	$this->getLevelService()->sortLevels($request->request->get('ids'));
			return $this->createJsonResponse(true);
	    }

	    public function zoneAction(Request $request)
	    {
	    	return $this->render('TopxiaAdminBundle:Userlevel:zone.html.twig');
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