<?php
namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Common\ServiceKernel;

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
            $this->getMoneyService()->countMoneyRecords($conditions),
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

		return $this->render('Money:records.html.twig', array(
			'records' => $records,
            'paginator' => $paginator,
            'users' => $users
		));
	}

	protected function getMoneyService()
    {
        return $this->getServiceKernel()->createService('Order:MoneyService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}