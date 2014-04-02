<?php
namespace Vip\VipBundle\Controller;

use Topxia\AdminBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class VipLevelAdminController extends BaseController 
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
			$memberlevel['memberNum'] = $this->getVipService()->searchMembersCount(array( 'level' => $memberlevel['id'] ));
		}
		return $this->render('VipBundle:VipLevelAdmin:index.html.twig', array(
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
			return $this->redirect($this->generateUrl('admin_vip_level'));
		}

		return $this->render('VipBundle:VipLevelAdmin:memberlevel.html.twig');
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
			return $this->redirect($this->generateUrl('admin_vip_level'));
		}

        return $this->render('VipBundle:VipLevelAdmin:memberlevel.html.twig', array(
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
		return $this->render('VipBundle:VipLevelAdmin:picture-modal.html.twig');
	}

	public function iconAction(Request $request)
	{
		return $this->render('VipBundle:VipLevelAdmin:icon-modal.html.twig');
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
			throw $this->createNotFoundException('会员类型不存在!');
		}
		return $level;
	}

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }    

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}