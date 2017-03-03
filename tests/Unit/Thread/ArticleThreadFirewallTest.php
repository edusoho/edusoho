<?php

namespace Tests\Unit\Thread;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;

class ArticleThreadFirewallTest extends BaseTestCase
{
    public function testaccessPostCreate()
    {
        $user = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $user1 = $this->getCurrentUser();
        if ($result = $user1->isLogin()) {
            return $result;
        }
        $this->assertFalse($result);
    }

    public function testaccessPostDelete()
    {
        $user = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $user1 = $this->getCurrentUser();
        $user1Id = '1';

        if ($result = $user1->isLogin()) {
            if ($result = $user1Id == $user['id'] ? true : false) {
                return $result;
            }
        }
        $this->assertFalse($result);
    }

    public function testaccessPostVote()
    {
        $user = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $user1 = $this->getCurrentUser();
        if ($result = $user1->isLogin()) {
            return $result;
        }
        $this->assertFalse($result);
    }

    protected function createUser()
    {
        $user = array();
        $user['email'] = 'user@user.com';
        $user['nickname'] = 'user';
        $user['password'] = 'user';
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');

        return $user;
    }

    public function getCurrentUser()
    {
        return $this->getServiceKernel()->getCurrentUser();
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
