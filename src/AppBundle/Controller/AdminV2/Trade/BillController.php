<?php

namespace AppBundle\Controller\AdminV2\Trade;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Account\Service\AccountProxyService;
use Biz\Common\CommonException;
use Codeages\Biz\Pay\Service\AccountService;
use Codeages\Biz\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\MathToolkit;

class BillController extends BaseController
{
    public function indexAction(Request $request, $type)
    {
        if (!in_array($type, array('coin', 'money'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
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

        return $this->render("admin-v2/trade/bill/{$type}.html.twig", array(
            'cashes' => $cashes,
            'paginator' => $paginator,
            'users' => $users,
            'account' => $account,
            'outflow' => $outflow,
            'inflow' => $inflow,
            'trades' => $trades,
        ));
    }

    private function getInflowAndOutflow($conditions)
    {
        if (!empty($conditions['platform']) && 'none' == $conditions['platform']) {
            //platform 为none时，代表退款，去掉platform查询条件，网校收入支出受此查询条件影响
            unset($conditions['platform']);
        }
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

    /**
     * @return AccountService
     */
    protected function getAccountService()
    {
        return $this->createService('Pay:AccountService');
    }

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->createService('Pay:PayService');
    }
}
