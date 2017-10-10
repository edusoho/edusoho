<?php

namespace AppBundle\Controller\Admin;

use Biz\System\Service\SettingService;
use AppBundle\Common\Paginator;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\BlockToolkit;
use AppBundle\Common\StringToolkit;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Common\MathToolkit;

class BillController extends BaseController
{
    public function billAction(Request $request, $type)
    {
        if (!in_array($type, array('coin', 'money'))) {
            throw $this->createNotFoundException('not exist');
        }

        $account = $this->getAccountService()->getUserBalanceByUserId(0);
        $conditions = $this->buildConditions($request->query->all());
        $conditions['amount_type'] = $type;
        $conditions['user_id'] = 0;

        $paginator = new Paginator(
            $request,
            $this->getAccountProxyService()->countUserCashflows($conditions),
            20
        );

        $cashes = $this->getAccountProxyService()->searchUserCashflows(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($cashes as &$cash) {
            $cash = MathToolkit::multiply($cash, array('amount'), 0.01);
        }
        $buyerIds = ArrayToolkit::column($cashes, 'buyer_id');
        $users = $this->getUserService()->findUsersByIds($buyerIds);

        list($inflow, $outflow) = $this->getInflowAndOutflow($conditions);

        return $this->render("admin/bill/{$type}.html.twig", array(
            'cashes' => $cashes,
            'paginator' => $paginator,
            'users' => $users,
            'account' => $account,
            'outflow' => $outflow,
            'inflow' => $inflow,
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

    private function buildConditions($conditions)
    {
        if (!empty($conditions['startTime'])) {
            $conditions['created_time_GTE'] = strtotime($conditions['startTime']);
            unset($conditions['startTime']);
        }
        if (!empty($conditions['endTime'])) {
            $conditions['created_time_LT'] = strtotime($conditions['endTime']);
            unset($conditions['endTime']);
        }

        if (!empty($conditions['keyword']) && !empty($conditions['keywordType'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        return $conditions;
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
}