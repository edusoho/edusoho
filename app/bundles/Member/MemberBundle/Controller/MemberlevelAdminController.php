<?php
	namespace Member\MemberBundle\Controller;

	use Topxia\AdminBundle\Controller\BaseController;
	use Symfony\Component\HttpFoundation\Request;
	use Topxia\Common\ArrayToolkit;
	use Topxia\Common\Paginator;

	class MemberlevelAdminController extends BaseController 
	{

		public function indexAction (Request $request)
		{	
			$conditions = array_filter($request->query->all());
			$paginator = new Paginator(
	            $this->get('request'),
	            $this->getLevelService()->searchLevelsCount($conditions),
	            20
	        );

			$memberlevels = $this->getLevelService()->searchLevels(
	            $conditions,
	            $paginator->getOffsetCount(),
	            $paginator->getPerPageCount()
	        );
			foreach ($memberlevels as &$memberlevel) {
				$memberlevel['memberNum'] = $this->getMemberService()->searchMembersCount(array( 'level' => $memberlevel['id'] ));
			}
			return $this->render('MemberBundle:MemberlevelAdmin:index.html.twig', array(
	            'memberlevels' => $memberlevels,
	            'paginator' => $paginator
	        ));
		}

		public function createAction (Request $request)
   		{   
	        if ('POST' == $request->getMethod()) {
	        	$conditions = $request->request->all();
	        	if(isset($conditions['monthType'])) {
	        		unset($conditions['monthType']);
	        	} else { 
	        		$conditions['monthPrice'] = 0.00; 
	        	}
	        	if(isset($conditions['yearType'])) {
	        		unset($conditions['yearType']);
	        	} else { 
	        		$conditions['yearPrice'] = 0.00; 
	        	}

				$memberlevel = $this->getLevelService()->createLevel($conditions);
				if($memberlevel){
					$this->setFlashMessage('success', '会员类型已保存！');
				}
				return $this->redirect($this->generateUrl('admin_user_level'));
			}

			return $this->render('MemberBundle:MemberlevelAdmin:memberlevel.html.twig');
    	}

    	public function updateAction (Request $request,$id)
   		{   
   			$memberlevel = $this->getLevelService()->getLevel($id);
			if (empty($memberlevel)) {
				throw $this->createNotFoundException();
			}

	        if ('POST' == $request->getMethod()) {
	        	$conditions = $request->request->all();
	        	if(isset($conditions['monthType'])) {
	        		unset($conditions['monthType']);
	        	} else { 
	        		$conditions['monthPrice'] = 0.00; 
	        	}
	        	if(isset($conditions['yearType'])) {
	        		unset($conditions['yearType']);
	        	} else { 
	        		$conditions['yearPrice'] = 0.00;  
	        	}

				$memberlevel = $this->getLevelService()->updateLevel($id, $conditions);

				if($memberlevel){
					$this->setFlashMessage('success', '会员类型已更新！');
				}
				return $this->redirect($this->generateUrl('admin_user_level'));
			}

	        return $this->render('MemberBundle:MemberlevelAdmin:memberlevel.html.twig', array(
			'memberlevel' => $memberlevel));
	    }

    	public function deleteAction(Request $request,$id)
		{
			$this->getLevelService()->deleteLevel($id);
			return $this->createJsonResponse(true);
		}

		public function onAction(Request $request,$id)
		{
			$this->getLevelService()->onLevel($id);
			return $this->createJsonResponse(true);
		}

		public function offAction(Request $request,$id)
		{
			$this->getLevelService()->offLevel($id);
			return $this->createJsonResponse(true);
		}

		public function pictureAction(Request $request)
		{
			return $this->render('MemberBundle:MemberlevelAdmin:picture-modal.html.twig');
		}

		public function iconAction(Request $request)
		{
			return $this->render('MemberBundle:MemberlevelAdmin:icon-modal.html.twig');
		}

	    public function sortAction(Request $request)
	    {
	    	$this->getLevelService()->sortLevels($request->request->get('ids'));
			return $this->createJsonResponse(true);
	    }

	    public function zoneAction(Request $request)
	    {
	        $memberZone = $this->getSettingService()->get('memberZone', array());

	        $default = array(
	            'enabled'=> 0,
	            'upgradeLimit' => 30,
	            'courseLimit' => 0,
	        );

	        $memberZone = array_merge($default, $memberZone);

	        if ($request->getMethod() == 'POST') {
	            $memberZone = $request->request->all();
	            $this->getSettingService()->set('memberZone', $memberZone);
	            $this->getLogService()->info('memberZone', 'update_memberZone', "更新会员专区设置", $memberZone);
	            $this->setFlashMessage('success','会员专区设置已保存！');
	        }

	    	return $this->render('MemberBundle:MemberlevelAdmin:zone.html.twig', array(
	    		'memberZone' => $memberZone
	    	));
	    }

		private function getLevel($id)
		{
			$level = $this->getLevelService()->getLevel($id);
			if (empty($level)) {
				throw $this->createNotFoundException('会员类型不存在!');
			}
			return $level;
		}

	    protected function getMemberService()
	    {
	        return $this->getServiceKernel()->createService('Member:Member.MemberService');
	    }    

	    protected function getLevelService()
	    {
	        return $this->getServiceKernel()->createService('Member:Member.LevelService');
	    }

	    protected function getSettingService()
	    {
	        return $this->getServiceKernel()->createService('System.SettingService');
	    }

}