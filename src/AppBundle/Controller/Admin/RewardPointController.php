<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;

class RewardPointController extends BaseController
{
    public function indexAction(Request $request)
    {
        if (!$this->getAccountService()->hasRewardPointPermission()) {
            return $this->createMessageResponse('error', '积分没有开启,请联系管理员！');
        }
        $fields = $request->query->all();
        $conditions = array(
            'keyword' => '',
            'keywordType' => '',
        );
        $conditions = array_merge($conditions, $fields);
        $userProfiles = $this->getUserService()->searchUserProfiles(
            $conditions,
            array(),
            0,
            PHP_INT_MAX
        );

        if (!empty($userProfiles)) {
            $userIds = ArrayToolkit::column($userProfiles, 'id');
            $conditions['userIds'] = $userIds;
        }
        $userProfiles = ArrayToolkit::index($userProfiles, 'id');
        $userCount = $this->getUserService()->countUsers($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $userCount,
            20
        );
        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (!empty($users)) {
            $userIds = ArrayToolkit::column($users, 'id');
            $conditions['userIds'] = $userIds;

            $accounts = $this->getAccountService()->searchAccounts(
                $conditions,
                array(),
                0,
                PHP_INT_MAX
            );

            $accounts = ArrayToolkit::index($accounts, 'userId');
            foreach ($accounts as &$account) {
                $accountOutFlow = $this->getAccountFlowService()->sumAccountOutFlowByUserId($account['userId']);
                $account['outFlow'] = $accountOutFlow;
            }
        }

        return $this->render('admin/reward-point/index.html.twig', array(
            'users' => empty($userProfiles) ? array() : $users,
            'userProfiles' => empty($userProfiles) ? array() : $userProfiles,
            'accounts' => empty($accounts) ? array() : $accounts,
            'paginator' => $paginator,
        ));
    }

    public function grantAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $account = $this->getAccountService()->getAccountByUserId($id);

        if ($request->getMethod() === 'POST') {
            $profile = $request->request->all();
            $this->getAccountService()->grantRewardPoint($id, $profile);

            return $this->redirect($this->generateUrl('admin_reward_point_account'));
        }

        return $this->render('admin/reward-point/grant-modal.html.twig',
            array(
                'user' => $user,
                'account' => $account,
            ));
    }

    public function detailAction(Request $request, $id)
    {
        $conditions['userId'] = $id;
        $user = $this->getUserService()->getUser($id);
        $accountFlowCount = $this->getAccountFlowService()->countAccountFlows($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $accountFlowCount,
            10
        );

        $accountFlows = $this->getAccountFlowService()->searchAccountFlows(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/reward-point/detail-modal.html.twig',
            array(
                'accountFlows' => $accountFlows,
                'user' => $user,
                'paginator' => $paginator,
            ));
    }

    public function deductionAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $account = $this->getAccountService()->getAccountByUserId($id);

        if ($request->getMethod() === 'POST') {
            $profile = $request->request->all();
            $this->getAccountService()->deductionRewardPoint($id, $profile);

            return $this->redirect($this->generateUrl('admin_reward_point_account'));
        }

        return $this->render('admin/reward-point/deduction-modal.html.twig',
            array(
                'user' => $user,
                'account' => $account,
            ));
    }

    public function logsAction(Request $request)
    {
        if (!$this->getAccountService()->hasRewardPointPermission()) {
            return $this->createMessageResponse('error', '积分没有开启,请联系管理员！');
        }
        $conditions = $request->query->all();
        $conditions['module'] = 'admin_reward_point_account_flow';
        $paginator = new Paginator(
            $request,
            $this->getLogService()->searchLogCount($conditions),
            20
        );

        $logs = $this->getLogService()->searchLogs(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $operators = $this->getUserService()->findUsersByIds(ArrayToolkit::column($logs, 'userId'));
        $datas = ArrayToolkit::column($logs, 'data');
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($datas, 'userId'));

        return $this->render('admin/reward-point/logs.html.twig', array(
            'logs' => $logs,
            'paginator' => $paginator,
            'operators' => $operators,
            'users' => $users,
        ));
    }

    protected function getAccountService()
    {
        return $this->createService('RewardPoint:AccountService');
    }

    protected function getAccountFlowService()
    {
        return $this->createService('RewardPoint:AccountFlowService');
    }
}
