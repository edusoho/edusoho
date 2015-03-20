<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MoneyRecordController extends BaseController
{

	public function indexAction(Request $request)
	{	
		$user = $this->getCurrentUser();

    	$conditions = array(
    		'userId' => $user['id'],
            'type' =>'income',
            'status' => 'finished'
		);

        $paginator = new Paginator(
            $request,
            $this->getMoneyService()->searchMoneyRecordsCount($conditions),
            15
        );
        $incomeRecords = $this->getMoneyService()->searchMoneyRecords(
        	$conditions,
        	'latest',
        	$paginator->getOffsetCount(),
        	$paginator->getPerPageCount()
    	);

		return $this->render('TopxiaWebBundle:MoneyRecord:index.html.twig',array(
        	'incomeRecords' => $incomeRecords,
            'paginator' => $paginator
        ));
	}

	public function payoutAction(Request $request)
	{   
        $user = $this->getCurrentUser();

        $conditions = array(
            'userId' => $user['id'],
            'type' =>'payout',
            'status' => 'finished'
        );

        $paginator = new Paginator(
            $request,
            $this->getMoneyService()->searchMoneyRecordsCount($conditions),
            15
        );

        $payoutRecords = $this->getMoneyService()->searchMoneyRecords(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
		return $this->render('TopxiaWebBundle:MoneyRecord:payout.html.twig',array(
            'payoutRecords' => $payoutRecords,
            'paginator' => $paginator
        ));
	}

	private function getMoneyService()
    {
        return $this->getServiceKernel()->createService('Order.MoneyService');
    }
}