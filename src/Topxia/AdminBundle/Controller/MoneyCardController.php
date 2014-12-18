<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class MoneyCardController extends BaseController
{

	public function indexAction (Request $request)
    {
		$conditions = $request->query->all();

        $paginator = new Paginator(
            $this->get('request'),
           	$this->getMoneyCardService()->searchMoneyCardsCount($conditions),
           	20
        );

        $moneyCards = $this->getMoneyCardService()->searchMoneyCards(
            $conditions,
            array('id', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

		return $this->render('TopxiaAdminBundle:MoneyCard:index.html.twig', array(
			'moneyCards'      => $moneyCards ,
            'paginator'  => $paginator
			));
	}

    public function indexBatchAction (Request $request)
    {
        $conditions = $request->query->all();

        $paginator = new Paginator(
            $this->get('request'),
            $this->getMoneyCardService()->searchBatchsCount($conditions),
            20
        );

        $batchs = $this->getMoneyCardService()->searchBatchs(
            $conditions,
            array('id', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaAdminBundle:MoneyCard:index-batch.html.twig', array(
            'batchs'    => $batchs,
            'paginator' => $paginator
            ));
    }

	public function getPasswordAction (Request $request, $id)
    {
		$moneyCard = $this->getMoneyCardService()->getMoneyCard($id);

        $this->getLogService()->info('money_card', 'show_password', "查询了卡号为{$moneyCard['cardId']}密码");

        return $this->render('TopxiaAdminBundle:MoneyCard:show-password-modal.html.twig', array(
            'moneyCardPassword' => $moneyCard['password']
        ));
	}

    public function createAction (Request $request)
    {
        if ($request->getMethod() == 'POST') {

            $moneyCardData = $request->request->all();

            $batch = $this->getMoneyCardService()->createMoneyCard($moneyCardData);

            return $this->redirect($this->generateUrl('admin_money_card'));
        }
        return $this->render('TopxiaAdminBundle:MoneyCard:create-modal.html.twig');
    }

    public function lockAction ($id)
    {
        $moneyCard = $this->getMoneyCardService()->lockMoneyCard($id);

        return $this->render('TopxiaAdminBundle:MoneyCard:money-card-table-tr.html.twig', array(
            'moneyCard' => $this->getMoneyCardService()->getMoneyCard($id),
        ));
    }

    public function unlockAction ($id)
    {
        $moneyCard = $this->getMoneyCardService()->unlockMoneyCard($id);

        return $this->render('TopxiaAdminBundle:MoneyCard:money-card-table-tr.html.twig', array(
            'moneyCard' => $this->getMoneyCardService()->getMoneyCard($id),
        ));
    }

    public function deleteAction (Request $request,$id)
    {
        if ($request->getMethod() == 'POST') {
            $moneyCard = $this->getMoneyCardService()->getMoneyCard($id);

            $this->getMoneyCardService()->deleteMoneyCard($id);
        }

        return $this->redirect($this->generateUrl('admin_money_card'));
    }

    public function lockBatchAction ($id)
    {
        $batch = $this->getMoneyCardService()->lockBatch($id);

        return $this->render('TopxiaAdminBundle:MoneyCard:batch-table-tr.html.twig', array(
            'batch' => $this->getMoneyCardService()->getBatch($id),
        ));
    }

    public function unlockBatchAction ($id)
    {
        $batch = $this->getMoneyCardService()->unlockBatch($id);

        return $this->render('TopxiaAdminBundle:MoneyCard:batch-table-tr.html.twig', array(
            'batch' => $this->getMoneyCardService()->getBatch($id),
        ));
    }

    public function deleteBatchAction (Request $request, $id)
    {
        if ($request->getMethod() == 'POST') {
            $this->getMoneyCardService()->deleteBatch($id);
        }

        return $this->redirect($this->generateUrl('admin_money_card_batch'));
    }

    public function exportCsvAction (Request $request, $batchId)
    {
        $batch = $this->getMoneyCardService()->getBatch($batchId);

        $moneyCards = $this->getMoneyCardService()->searchMoneyCards(
            array('batchId' => $batchId),
            array('id', 'DESC'),
            1,
            $batch['number']
        );

        $str = "卡号,密码,批次"."\r\n";

        $moneyCards = array_map(function($moneyCard){
            $card['cardId']   = $moneyCard['cardId'];
            $card['password'] = $moneyCard['password'];
            $card['batchId']  = $moneyCard['batchId'];
            return implode(',',$card);
        }, $moneyCards);

        $str .= implode("\r\n",$moneyCards);

        $filename = "cards-".$batchId."-".date("YmdHi").".csv";

        $userId = $this->getCurrentUser()->id;
        $this->getLogService()->info('money_card_export', 'export', "导出了批次为{$batchId}的充值卡");

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

	private function getMoneyCardService()
    {
        return $this->getServiceKernel()->createService('MoneyCard.MoneyCardService');
    }

    protected function getLogService ()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }
}