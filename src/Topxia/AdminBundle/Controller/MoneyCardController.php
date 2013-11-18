<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Response;

class MoneyCardController extends BaseController {

	public function indexAction (Request $request) {

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

    public function indexBatchAction (Request $request) {

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

	public function getPasswordAction (Request $request, $id) {

		$moneyCard = $this->getMoneyCardService()->getMoneyCard($id);

        $userId = $this->getCurrentUser()->id;
        $this->getLogService()->info('money_card', 'show_password', "ID为{$userId}的管理员查询了卡号为{$moneyCard['cardId']}密码");

        return $this->render('TopxiaAdminBundle:MoneyCard:show-password-modal.html.twig', array(
            'moneyCardPassword' => $moneyCard['password']
        ));
	}

    public function createAction (Request $request) {

        if ($request->getMethod() == 'POST') {

            $formData = $request->request->all();

            $moneyCardData['money']          = $formData['money'];
            $moneyCardData['cardPrefix']     = $formData['cardPrefix'];
            $moneyCardData['cardMedian']     = $formData['cardMedian'];
            $moneyCardData['passwordMedian'] = $formData['passwordMedian'];
            $moneyCardData['validTime']      = $formData['validTime'];
            $moneyCardData['number']         = $formData['number'];
            $moneyCardData['disc']           = $formData['disc'];

            $batch = $this->getMoneyCardService()->createMoneyCard($moneyCardData);

            $this->getLogService()->info('money_card_batch', 'create', "管理员创建新批次充值卡,卡号前缀为({$batch['cardPrefix']}),批次为({$batch['id']})");

            return $this->redirect($this->generateUrl('admin_money_card'));
        }
        return $this->render('TopxiaAdminBundle:MoneyCard:create-modal.html.twig');
    }

    public function lockAction ($id) {

        $moneyCard = $this->getMoneyCardService()->lockMoneyCard($id);

        $userId = $this->getCurrentUser()->id;
        $this->getLogService()->info('money_card', 'lock', "ID为{$userId}的管理员作废了卡号为{$moneyCard['cardId']}的充值卡");

        return $this->render('TopxiaAdminBundle:MoneyCard:money-card-table-tr.html.twig', array(
            'moneyCard' => $this->getMoneyCardService()->getMoneyCard($id),
        ));
    }

    public function unlockAction ($id) {

        $moneyCard = $this->getMoneyCardService()->unlockMoneyCard($id);

        $userId = $this->getCurrentUser()->id;
        $this->getLogService()->info('money_card', 'unlock', "ID为{$userId}的管理员启用了卡号为{$moneyCard['cardId']}的充值卡");

        return $this->render('TopxiaAdminBundle:MoneyCard:money-card-table-tr.html.twig', array(
            'moneyCard' => $this->getMoneyCardService()->getMoneyCard($id),
        ));
    }

    public function deleteAction (Request $request,$id) {

        if ($request->getMethod() == 'POST') {
            $moneyCard = $this->getMoneyCardService()->getMoneyCard($id);

            $this->getMoneyCardService()->deleteMoneyCard($id);

            $userId = $this->getCurrentUser()->id;
            $this->getLogService()->info('money_card', 'delete', "ID为{$userId}的管理员删除了卡号为{$moneyCard['cardId']}的充值卡");
        }

        return $this->redirect($this->generateUrl('admin_money_card'));
    }

    public function lockBatchAction ($id) {

        $batch = $this->getMoneyCardService()->lockBatch($id);

        $userId = $this->getCurrentUser()->id;
        $this->getLogService()->info('money_card_batch', 'lock', "ID为{$userId}的管理员作废了批次为{$batch['id']}的充值卡");

        return $this->render('TopxiaAdminBundle:MoneyCard:batch-table-tr.html.twig', array(
            'batch' => $this->getMoneyCardService()->getBatch($id),
        ));
    }

    public function unlockBatchAction ($id) {

        $batch = $this->getMoneyCardService()->unlockBatch($id);

        $userId = $this->getCurrentUser()->id;
        $this->getLogService()->info('money_card_batch', 'unlock', "ID为{$userId}的管理员启用了批次为{$batch['id']}的充值卡");

        return $this->render('TopxiaAdminBundle:MoneyCard:batch-table-tr.html.twig', array(
            'batch' => $this->getMoneyCardService()->getBatch($id),
        ));
    }

    public function deleteBatchAction (Request $request, $id) {

        if ($request->getMethod() == 'POST') {
            $this->getMoneyCardService()->deleteBatch($id);

            $userId = $this->getCurrentUser()->id;
            $this->getLogService()->info('money_card_batch', 'delete', "ID为{$userId}的管理员删除了批次为{$id}的充值卡");
        }

        return $this->redirect($this->generateUrl('admin_money_card_batch'));
    }

    public function exportCsvAction (Request $request, $batchId) {

        $moneyCards = $this->getMoneyCardService()->exportCsv(
        array('batchId' => $batchId),
        array('id', 'DESC')
        );

        $userId = $this->getCurrentUser()->id;
        $this->getLogService()->info('money_card_export', 'export', "ID为{$userId}的管理员导出了批次为{$batchId}的充值卡");

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$moneyCards['filename'].'"');
        $response->headers->set('Content-length', strlen($moneyCards['str']));
        // $response->sendHeaders();
        $response->setContent($moneyCards['str']);

        return $response;
    }

	private function getMoneyCardService() {

        return $this->getServiceKernel()->createService('MoneyCard.MoneyCardService');
    }

    protected function getLogService () {

        return $this->getServiceKernel()->createService('System.LogService');
    }
}