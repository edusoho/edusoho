<?php

namespace Org\Service\Org\Tests;

use Topxia\Common\ArrayToolkit;
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

    public function testFindOrgsByIds()
    {
        $user = $this->setCurrent();

        $org = $this->mookOrg($name = "edusoho");
        $org = $this->getOrgService()->createOrg($org);

        $childOrg             = $this->mookOrg($name = "tech");
        $childOrg['parentId'] = $org['id'];
        $childOrg             = $this->getOrgService()->createOrg($childOrg);

        $orgs = $this->getOrgService()->findOrgsByIds(array($org['id'], $childOrg['id']));

        $this->assertEquals(2, count($orgs));
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

    public function testfindOrgsByPrefixOrgCode()
    {
        $org = $this->mookOrg($name = "edusoho");
        $org = $this->getOrgService()->createOrg($org);

        $childOrg             = $this->mookOrg($name = "tech");
        $childOrg['parentId'] = $org['id'];
        $childOrg             = $this->getOrgService()->createOrg($childOrg);

        $orgs     = $this->getOrgService()->findOrgsByPrefixOrgCode($org['orgCode']);
        $orgsless = $this->getOrgService()->findOrgsByPrefixOrgCode($childOrg['orgCode']);

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

    public function testSortOrg()
    {
        $org  = $this->mookOrg($name = "edusoho");
        $org1 = $this->mookOrg($name = "edusoho1");
        $org  = $this->getOrgService()->createOrg($org);
        $org  = $this->getOrgService()->createOrg($org1);

        $orgs   = $this->getOrgService()->findOrgsByPrefixOrgCode();
        $seqs   = ArrayToolkit::column($orgs, 'seq');
        $orgIds = ArrayToolkit::column($orgs, 'id');
        $this->getOrgService()->sortOrg($orgIds);

        $orgs = $this->getOrgService()->findOrgsByPrefixOrgCode();

        $sortSeqs = ArrayToolkit::column($orgs, 'seq');
        $this->assertGreaterThan(array_sum($seqs), array_sum($sortSeqs));
    }

    public function testBatchUpdateOrg()
    {
        $magic = $this->getServiceKernel()->createService('System.SettingService')->set('magic', array('enable_org' => 0));
        $magic = $this->getServiceKernel()->createService('System.SettingService')->get('magic');

        $org  = $this->mookOrg($name = "edusoho");
        $org1 = $this->mookOrg($name = "edusoho1");
        $org  = $this->getOrgService()->createOrg($org);
        $org1 = $this->getOrgService()->createOrg($org1);

        $course = array(
            'title'   => 'online test course 1',
            'orgCode' => $org['orgCode']
        );
        $createCourse = $this->getCourseService()->createCourse($course);

        $this->assertEquals($org['id'], $createCourse['orgId']);
        $this->assertEquals($org['orgCode'], $createCourse['orgCode']);

        $this->getOrgService()->batchUpdateOrg('course', $createCourse['id'], $org1['orgCode']);
        $course = $this->getCourseService()->getCourse($createCourse['id']);

        $this->assertEquals($org['id'], $course['orgId']);
        $this->assertEquals($org['orgCode'], $course['orgCode']);
    }

    public function testBatchUpdateOrgwithEnableOrg()
    {
        $magic = $this->getServiceKernel()->createService('System.SettingService')->set('magic', array('enable_org' => 1));
        $magic = $this->getServiceKernel()->createService('System.SettingService')->get('magic');

        $org  = $this->mookOrg($name = "edusoho");
        $org1 = $this->mookOrg($name = "edusoho1");
        $org  = $this->getOrgService()->createOrg($org);
        $org1 = $this->getOrgService()->createOrg($org1);

        $course = array(
            'title'   => 'online test course 1',
            'orgCode' => $org['orgCode']
        );
        $createCourse = $this->getCourseService()->createCourse($course);

        $this->assertEquals($org['id'], $createCourse['orgId']);
        $this->assertEquals($org['orgCode'], $createCourse['orgCode']);

        $this->getOrgService()->batchUpdateOrg('course', $createCourse['id'], $org1['orgCode']);
        $course = $this->getCourseService()->getCourse($createCourse['id']);

        $this->assertEquals($org1['id'], $course['orgId']);
        $this->assertEquals($org1['orgCode'], $course['orgCode']);

    }

    private function mookOrg($name)
    {
        $org         = array();
        $org['name'] = $name;
        $org['code'] = $name;
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

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
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
