<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\MathToolkit;

class BillController extends BaseController
{
    public function billAction(Request $request, $type)
    {
        if (!in_array($type, array('coin', 'money'))) {
            throw $this->createNotFoundException('not exist');
        }

        $account = $this->getAccountService()->getUserBalanceByUserId(0);

        $conditions = $request->query->all();
        $conditions['amount_type'] = $type;
        $conditions['user_id'] = 0;

        $paginator = new Paginator(
            $request,
            $this->getAccountProxyService()->countCashflows($conditions),
            20
        );

        $cashes = $this->getAccountProxyService()->searchCashflows(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $tradeSns = ArrayToolkit::column($cashes, 'trade_sn');
        $trades = $this->getPayService()->findTradesByTradeSn($tradeSns);
        $trades = ArrayToolkit::index($trades, 'trade_sn');

        foreach ($cashes as &$cash) {
            $cash = MathToolkit::multiply($cash, array('amount'), 0.01);
        }
        $buyerIds = ArrayToolkit::column($cashes, 'buyer_id');
        $users = $this->getUserService()->findUsersByIds($buyerIds);

        list($inflow, $outflow) = $this->getInflowAndOutflow($conditions);
        $isUserManager = $this->getCurrentUser()->hasPermission('admin_user_manage');

        return $this->render("admin/bill/{$type}.html.twig", array(
            'cashes' => $cashes,
            'paginator' => $paginator,
            'users' => $users,
            'account' => $account,
            'outflow' => $outflow,
            'inflow' => $inflow,
            'trades' => $trades,
            'isUserManager' => $isUserManager,
        ));
    }

    private function getInflowAndOutflow($conditions)
    {
        $conditions['type'] = 'outflow';
        $amountOutflow = $this->getAccountProxyService()->sumColumnByConditions('amount', $conditions);
        $conditions['type'] = 'inflow';
        $amountInflow = $this->getAccountProxyService()->sumColumnByConditions('amount', $conditions);

        return array($amountInflow * 0.01, $amountOutflow * 0.01);
    }

    /**
     * @return AccountProxyService
     */
    protected function getAccountProxyService()
    {
        return $this->createService('Account:AccountProxyService');
    }

    protected function getAccountService()
    {
        return $this->createService('Pay:AccountService');
    }

    protected function getPayService()
    {
        return $this->createService('Pay:PayService');
    }
}
