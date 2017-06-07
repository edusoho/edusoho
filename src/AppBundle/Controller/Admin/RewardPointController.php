<?php
namespace AppBundle\Controller\Admin;


use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;

class RewardPointController extends BaseController
{
    public function indexAction(Request $request)
    {
        $fields = $request->query->all();

        $conditions =  $fields;

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
        if (!empty($users)){
            $userIds = ArrayToolkit::column($users, 'id');
            $conditions['userIds'] = $userIds;
            $accounts = $this->getAccountService()->searchAccounts(
                $conditions,
                array(),
                0,
                PHP_INT_MAX
            );
            $userProfiles = $this->getUserService()->searchUserProfiles(
                $conditions,
                array(),
                0,
                PHP_INT_MAX
            );
            $userProfiles = ArrayToolkit::index($userProfiles, 'id');
            $accounts = ArrayToolkit::index($accounts, 'userId');

        }


        return $this->render('admin/reward-point/index.html.twig', array(
            "users"=>$users,
            "userProfiles"=>$userProfiles,
            "accounts"=>$accounts,
            "paginator"=>$paginator,
        ));
    }

    protected function getAccountService()
    {
        return $this->createService('RewardPoint:AccountService');
    }
}
