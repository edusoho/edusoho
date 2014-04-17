<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class MoneyController extends BaseController
{
	public function recordsAction(Request $request)
	{	
		$conditions = $request->query->all(); 
		$conditions['status'] = 'finished';
		
		if(!empty($conditions['nickname'])) {
			$searchUser = $this->getUserService()->getUserByNickname($conditions['nickname']);
			$conditions['userId'] = $searchUser['id'];
		}

        $paginator = new Paginator(
            $request,
            $this->getMoneyService()->searchMoneyRecordsCount($conditions),
            15
        );

        $records = $this->getMoneyService()->searchMoneyRecords(
        	$conditions,
        	'latest',
        	$paginator->getOffsetCount(),
        	$paginator->getPerPageCount()
    	);

        $userIds = array();
        foreach ($records as $record) {
        	$userIds[] = $record['userId'];
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

		return $this->render('TopxiaAdminBundle:Money:records.html.twig', array(
			'records' => $records,
            'paginator' => $paginator,
            'users' => $users
		));
	}

	private function getMoneyService()
    {
        return $this->getServiceKernel()->createService('Order.MoneyService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}