<?php

namespace Tests\Unit\Org\Service;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseTestCase;
use Biz\Org\Service\OrgService;
use Biz\System\Service\SettingService;
use Biz\User\CurrentUser;

class OrgServiceTest extends BaseTestCase
{
    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateOrgException()
    {
        $org = array(
            'name' => 'name',
        );
        $this->getOrgService()->createOrg($org);
    }

    public function testGetOrg()
    {
        $user = $this->setCurrent();

        $org = $this->mookOrg($name = 'edusoho');
        $org = $this->getOrgService()->createOrg($org);

        $childOrg = $this->mookOrg($name = 'tech');
        $childOrg['parentId'] = $org['id'];
        $childOrg = $this->getOrgService()->createOrg($childOrg);

        $getOrg = $this->getOrgService()->getOrg($org['id']);
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

        $org = $this->mookOrg($name = 'edusoho');
        $org = $this->getOrgService()->createOrg($org);

        $childOrg = $this->mookOrg($name = 'tech');
        $childOrg['parentId'] = $org['id'];
        $childOrg = $this->getOrgService()->createOrg($childOrg);

        $orgs = $this->getOrgService()->findOrgsByIds(array($org['id'], $childOrg['id']));

        $this->assertEquals(2, count($orgs));
    }

    public function testCreateOrg()
    {
        $org = $this->mookOrg($name = 'edusoho');
        $org = $this->getOrgService()->createOrg($org);

        $this->assertEquals('edusoho', $org['name']);
    }

    /**
     * @expectedException \Biz\Org\OrgException
     * @expectedExceptionMessage exception.org.not_found
     */
    public function testUpdateOrgNotFoundException()
    {
        $this->getOrgService()->updateOrg(1, array());
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testUpdateOrgException()
    {
        $org = $this->mookOrg('edusoho');
        $org = $this->getOrgService()->createOrg($org);
        $fields = array(
            'name' => 'name',
        );
        $this->getOrgService()->updateOrg($org['id'], $fields);
    }

    public function testUpdateOrg()
    {
        $org = $this->mookOrg($name = 'edusoho');
        $org = $this->getOrgService()->createOrg($org);
        $this->assertEquals('edusoho', $org['name']);

        $org['name'] = 'updateEdu';
        $updateOrg = $this->getOrgService()->updateOrg($org['id'], $org);
        $this->assertEquals('updateEdu', $updateOrg['name']);
    }

    /**
     * @expectedException \Biz\Org\OrgException
     * @expectedExceptionMessage exception.org.not_found
     */
    public function testDeleteOrgNotFoundException()
    {
        $this->getOrgService()->deleteOrg(1);
    }

    public function testDeleteOrgHasParentId()
    {
        $parent = $this->mookOrg('parent');
        $parent = $this->getOrgService()->createOrg($parent);
        $child = array(
            'name' => 'child',
            'code' => 'child',
            'parentId' => $parent['id'],
        );
        $child = $this->getOrgService()->createOrg($child);

        $parent = $this->getOrgService()->getOrg($parent['id']);
        $this->assertEquals(1, $parent['childrenNum']);

        $this->getOrgService()->deleteOrg($child['id']);
        $parent = $this->getOrgService()->getOrg($parent['id']);
        $this->assertEquals(0, $parent['childrenNum']);
    }

    public function testDeleteOrg()
    {
        $org = $this->mookOrg($name = 'edusoho');
        $org = $this->getOrgService()->createOrg($org);
        $this->assertEquals('edusoho', $org['name']);

        $getOrg = $this->getOrgService()->getOrg($org['id']);
        $this->assertEquals('edusoho', $getOrg['name']);

        $this->getOrgService()->deleteOrg($org['id']);
        $getOrg = $this->getOrgService()->getOrg($org['id']);
        $this->assertNull($getOrg);
    }

    public function testFindOrgsByPrefixOrgCodeWithNull()
    {
        $org = $this->mookOrg('edusoho');
        $this->getOrgService()->createOrg($org);

        $orgs = $this->getOrgService()->findOrgsByPrefixOrgCode();
        $user = $this->getCurrentUser();
        $expectedOrg = $this->getOrgService()->getOrg($user['orgId']);
        $expected = $this->getOrgService()->findOrgsByPrefixOrgCode($expectedOrg['code']);

        $this->assertArraySternEquals($expected, $orgs);
    }

    public function testFindOrgsByPrefixOrgCode()
    {
        $org = $this->mookOrg($name = 'edusoho');
        $org = $this->getOrgService()->createOrg($org);

        $childOrg = $this->mookOrg($name = 'tech');
        $childOrg['parentId'] = $org['id'];
        $childOrg = $this->getOrgService()->createOrg($childOrg);

        $orgs = $this->getOrgService()->findOrgsByPrefixOrgCode($org['orgCode']);
        $orgsless = $this->getOrgService()->findOrgsByPrefixOrgCode($childOrg['orgCode']);

        $this->assertEquals(2, count($orgs));
        $this->assertEquals(1, count($orgsless));
    }

    public function testIsCodeAvailable()
    {
        $result = $this->getOrgService()->isCodeAvaliable('code', 'code');
        $this->assertTrue($result);

        $org = $this->mookOrg('code');
        $org = $this->getOrgService()->createOrg($org);

        $result = $this->getOrgService()->isCodeAvaliable($org['code'], $org['code']);
        $this->assertTrue($result);

        $result = $this->getOrgService()->isCodeAvaliable($org['code'], 'adeadeafaefea');
        $this->assertFalse($result);
    }

    public function testSwitchOrg()
    {
        $org = $this->mookOrg($name = 'edusoho');
        $org = $this->getOrgService()->createOrg($org);

        $org = $this->mookOrg($name = 'edusoho1');
        $org = $this->getOrgService()->createOrg($org);
        $this->getOrgService()->switchOrg($org['id']);

        $user = $this->getServiceKernel()->getCurrentUser();
        $this->assertEquals($org['orgCode'], $user->getSelectOrgCode());
    }

    public function testSortOrg()
    {
        $org = $this->mookOrg('edusoho');
        $org1 = $this->mookOrg('edusoho1');
        $org = $this->getOrgService()->createOrg($org);
        $org = $this->getOrgService()->createOrg($org1);

        $orgs = $this->getOrgService()->searchOrgs(array(), array(), 0, 2);

        $seqs = ArrayToolkit::column($orgs, 'seq');
        $orgIds = ArrayToolkit::column($orgs, 'id');
        $this->getOrgService()->sortOrg($orgIds);

        $orgs = $this->getOrgService()->searchOrgs(array(), array(), 0, 2);

        $sortSeqs = ArrayToolkit::column($orgs, 'seq');

        $this->assertGreaterThan(array_sum($seqs), array_sum($sortSeqs));
    }

    public function testBatchUpdateOrg()
    {
        $magic = $this->getSettingService()->set('magic', array('enable_org' => 0));
        $magic = $this->getSettingService()->get('magic');

        $org = $this->mookOrg($name = 'edusoho');
        $org1 = $this->mookOrg($name = 'edusoho1');
        $org = $this->getOrgService()->createOrg($org);
        $org1 = $this->getOrgService()->createOrg($org1);

        $createCourseSet = array(
            'title' => 'online test course 1',
            'orgCode' => $org['orgCode'],
            'courseSetId' => 1,
            'learnMode' => 'freeMode',
            'expiryMode' => 'forever',
            'type' => 'normal',
        );
        $createCourseSet = $this->getCourseSetService()->createCourseSet($createCourseSet);

        $this->assertEquals($org['id'], $createCourseSet['orgId']);
        $this->assertEquals($org['orgCode'], $createCourseSet['orgCode']);

        $this->getOrgService()->batchUpdateOrg('courseSet', $createCourseSet['id'], $org1['orgCode']);
        $createCourseSet = $this->getCourseSetService()->getCourseSet($createCourseSet['id']);

        $this->assertEquals($org['id'], $createCourseSet['orgId']);
        $this->assertEquals($org['orgCode'], $createCourseSet['orgCode']);
    }

    public function testBatchUpdateOrgwithEnableOrg()
    {
        $this->getSettingService()->set('magic', array('enable_org' => 1));

        $org = $this->mookOrg($name = 'edusoho');
        $org1 = $this->mookOrg($name = 'edusoho1');
        $org = $this->getOrgService()->createOrg($org);
        $org1 = $this->getOrgService()->createOrg($org1);

        $course = array(
            'title' => 'online test course 1',
            'type' => 'normal',
            'orgCode' => $org['orgCode'],
            'courseSetId' => 1,
            'learnMode' => 'freeMode',
            'expiryMode' => 'forever',
        );
        $createCourseSet = $this->getCourseSetService()->createCourseSet($course);

        $this->assertEquals($org['id'], $createCourseSet['orgId']);
        $this->assertEquals($org['orgCode'], $createCourseSet['orgCode']);

        $this->getOrgService()->batchUpdateOrg('courseSet', $createCourseSet['id'], $org1['orgCode']);
        $courseSet = $this->getCourseSetService()->getCourseSet($createCourseSet['id']);

        $this->assertEquals($org1['id'], $courseSet['orgId']);
        $this->assertEquals($org1['orgCode'], $courseSet['orgCode']);
    }

    public function testGetOrgByCode()
    {
        $org = $this->mookOrg('edusoho');
        $expected = $this->getOrgService()->createOrg($org);

        $org = $this->getOrgService()->getOrgByCode($org['code']);

        $this->assertArraySternEquals($expected, $org);

        $org = $this->getOrgService()->getOrgByCode('22311de');
        $this->assertEmpty($org);
    }

    public function testGeFullOrgNameById()
    {
        $org = $this->mookOrg('edusoho');
        $expected = $this->getOrgService()->createOrg($org);

        $orgName = $this->getOrgService()->geFullOrgNameById($expected['id']);

        $this->assertEquals($expected['name'], $orgName);

        $child = array(
            'name' => 'child',
            'code' => 'child',
            'parentId' => $expected['id'],
        );
        $child = $this->getOrgService()->createOrg($child);

        $orgName = $this->getOrgService()->geFullOrgNameById($child['id']);

        $this->assertEquals('edusoho->child', $orgName);
    }

    public function testIsNameAvailable()
    {
        $result = $this->getOrgService()->isNameAvaliable('code', 0, 1);
        $this->assertTrue($result);

        $org = $this->mookOrg('edusoho');
        $org = $this->getOrgService()->createOrg($org);

        $result = $this->getOrgService()->isNameAvaliable($org['name'], $org['parentId'], $org['id']);
        $this->assertTrue($result);

        $result = $this->getOrgService()->isNameAvaliable($org['name'], $org['parentId'], 22333);
        $this->assertFalse($result);
    }

    public function testFindRelatedModuleCounts()
    {
        $parent = $this->mookOrg('parent');
        $parent = $this->getOrgService()->createOrg($parent);
        $child = array(
            'name' => 'child',
            'code' => 'child',
            'parentId' => $parent['id'],
        );
        $child = $this->getOrgService()->createOrg($child);

        $count = $this->getOrgService()->findRelatedModuleCounts($parent['id']);
        $this->assertCount(1, $count);

        $count = $this->getOrgService()->findRelatedModuleCounts($child['id']);
        $this->assertCount(0, $count);
    }

    private function mookOrg($name)
    {
        $org = array();
        $org['name'] = $name;
        $org['code'] = $name;

        return $org;
    }

    private function setCurrent()
    {
        $user = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
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

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return OrgService
     */
    public function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
