<?php

namespace Org\Service\Org\Tests;

use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\BaseTestCase;

class OrgServiceTest extends BaseTestCase
{
    public function testGetOrg()
    {
        $user = $this->setCurrent();

        $org = $this->mookOrg($name = "edusoho");
        $org = $this->getOrgService()->createOrg($org);

        $childOrg             = $this->mookOrg($name = "tech");
        $childOrg['parentId'] = $org['id'];
        $childOrg             = $this->getOrgService()->createOrg($childOrg);

        $getOrg      = $this->getOrgService()->getOrg($org['id']);
        $getChildOrg = $this->getOrgService()->getOrg($childOrg['id']);

        $this->assertEquals('edusoho', $getOrg['name']);
        $this->assertEquals('0', $getOrg['parentId']);
        $this->assertEquals('1', $getOrg['depth']);

        $this->assertEquals($org['id'], $getChildOrg['parentId']);
        $this->assertEquals('2', $getChildOrg['depth']);

        $this->assertEquals('1', $getOrg['childrenNum']);
    }

    public function testCreateOrg()
    {
        $org = $this->mookOrg($name = "edusoho");
        $org = $this->getOrgService()->createOrg($org);

        $this->assertEquals('edusoho', $org['name']);
    }

    public function testUpdateOrg()
    {
        $org = $this->mookOrg($name = "edusoho");
        $org = $this->getOrgService()->createOrg($org);
        $this->assertEquals('edusoho', $org['name']);

        $org['name'] = 'updateEdu';
        $updateOrg   = $this->getOrgService()->updateOrg($org['id'], $org);
        $this->assertEquals('updateEdu', $updateOrg['name']);

    }

    public function testDeleteOrg()
    {
        $org = $this->mookOrg($name = "edusoho");
        $org = $this->getOrgService()->createOrg($org);
        $this->assertEquals('edusoho', $org['name']);

        $getOrg = $this->getOrgService()->getOrg($org['id']);
        $this->assertEquals('edusoho', $getOrg['name']);

        $this->getOrgService()->deleteOrg($org['id']);
        $getOrg = $this->getOrgService()->getOrg($org['id']);
        $this->assertNull($getOrg);
    }

    public function testfindOrgsStartByOrgCode()
    {
        $org = $this->mookOrg($name = "edusoho");
        $org = $this->getOrgService()->createOrg($org);

        $childOrg             = $this->mookOrg($name = "tech");
        $childOrg['parentId'] = $org['id'];
        $childOrg             = $this->getOrgService()->createOrg($childOrg);

        $orgs     = $this->getOrgService()->findOrgsStartByOrgCode($org['orgCode']);
        $orgsless = $this->getOrgService()->findOrgsStartByOrgCode($childOrg['orgCode']);

        $this->assertEquals(2, count($orgs));
        $this->assertEquals(1, count($orgsless));
    }

    public function testSwitchOrg()
    {
        $org = $this->mookOrg($name = "edusoho");
        $org = $this->getOrgService()->createOrg($org);

        $org = $this->mookOrg($name = "edusoho1");
        $org = $this->getOrgService()->createOrg($org);
        $this->getOrgService()->switchOrg($org['id']);

        $user = $this->getServiceKernel()->getCurrentUser();
        $this->assertEquals($org['orgCode'], $user->getSelectOrgCode());
    }

    private function mookOrg($name)
    {
        $org         = array();
        $org['name'] = $name;
        $org['code'] = $name;
        $org['seq']  = 0;
        return $org;
    }

    private function setCurrent()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
    }

    protected function createUser()
    {
        $user              = array();
        $user['email']     = "user@user.com";
        $user['nickname']  = "user";
        $user['password']  = "user";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');
        return $user;
    }

    public function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org:Org.OrgService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
