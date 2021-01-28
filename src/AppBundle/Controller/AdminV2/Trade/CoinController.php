<?php

namespace AppBundle\Controller\AdminV2\Trade;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\MathToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Account\Service\AccountProxyService;
use Codeages\Biz\Pay\Service\AccountService;
use Symfony\Component\HttpFoundation\Request;

class CoinController extends BaseController
{
    public function indexAction(Request $request)
    {
        $sort = $request->query->get('sort', 'amount');
        $direction = $request->query->get('direction', 'DESC');
        $conditions['except_user_id'] = 0;

        $fields = $request->query->all();

        if (!empty($fields)) {
            $convertCondition = $this->convertFiltersToCondition($fields);
            $conditions = array_merge($conditions, $convertCondition);
        }

        $schoolBalance = $this->getAccountProxyService()->getUserBalanceByUserId(0);

        if (isset($conditions['user_id'])) {
            if (0 == $conditions['user_id']) {
                $users = array();
                $balances = array();
                goto response;
            }
            $user = $this->getUserService()->getUser($conditions['user_id']);
            $users = array($conditions['user_id'] => $user);
            $balances = array();
            $balances[] = $this->getAccountProxyService()->getUserBalanceByUserId($conditions['user_id']);

            response :

            return $this->render('admin-v2/trade/coin/coin-user-records.html.twig', array(
                'schoolBalance' => $schoolBalance,
                'balances' => $balances,
                'users' => $users,
            ));
        }

        $systemUser = $this->getUserService()->getUserByType('system');

        $paginator = new Paginator(
            $this->get('request'),
            $this->getAccountProxyService()->countBalances(
                array(
                    'except_user_ids' => array(0, $systemUser['id']),
                )
            ),
            20
        );
        $balances = $this->getAccountProxyService()->searchBalances(
            array(
                'except_user_ids' => array(0, $systemUser['id']),
            ),
            array($sort => $direction),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($balances, 'user_id');

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('admin-v2/trade/coin/coin-user-records.html.twig', array(
            'schoolBalance' => $schoolBalance,
            'balances' => $balances,
            'paginator' => $paginator,
            'users' => $users,
        ));
    }

    public function flowDetailAction(Request $request)
    {
        $userId = $request->query->get('userId');
        $conditions['except_user_id'] = 0;
        $conditions['amount_type'] = 'coin';
        $conditions['user_id'] = $userId;

        $paginator = new Paginator(
            $this->get('request'),
            $this->getAccountProxyService()->countCashflows($conditions),
            20
        );

        $cashes = $this->getAccountProxyService()->searchCashflows(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($cashes as &$cash) {
            $cash = MathToolkit::multiply($cash, array('amount'), 0.01);
        }

        $user = $this->getUserService()->getUser($userId);

        return $this->render('admin-v2/trade/coin/flow-detail-modal.html.twig', array(
            'user' => $user,
            'cashes' => $cashes,
            'paginator' => $paginator,
        ));
    }

    protected function convertFiltersToCondition($condition)
    {
        if (!empty($condition['keyword'])) {
            $user = $this->getUserService()->getUserByNickname($condition['keyword']);
            $condition['user_id'] = $user ? $user['id'] : 0;
            unset($condition['keyword']);
        }

        return $condition;
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
}
