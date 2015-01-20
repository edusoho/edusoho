<?php 
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class AjaxUserProfileController extends BaseController
{
	public function updateAction(Request $request)
	{
		$user = $this->getCurrentUser();
		$data = $request->request->all();

		$this->getUserService()->updateUserProfile($user->id,$data);

		return $this->createJsonResponse(array('status'=>'success'));
	}
}