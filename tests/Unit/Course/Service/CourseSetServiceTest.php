<?php

namespace Tests\Unit\Course\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;

class CourseSetServiceTest extends BaseTestCase
{
    public function testCreateNormal()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertTrue($created['id'] > 0);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($created['id']);
        $this->assertTrue(1 === count($courses));
        $this->assertTrue(1 == $courses[0]['isDefault']);

        $course = array_shift($courses);
        $this->assertEquals('freeMode', $course['learnMode']);
    }

    public function testCreateNormalCourseSetWithDefaultCourseMode()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
            'learnMode' => 'lockMode',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertTrue($created['id'] > 0);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($created['id']);
        $this->assertTrue(1 === sizeof($courses));
        $this->assertTrue(1 == $courses[0]['isDefault']);

        $course = array_shift($courses);
        $this->assertEquals('lockMode', $course['learnMode']);
    }

    public function testCreateLive()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'live',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertTrue($created['id'] > 0);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($created['id']);
        $this->assertTrue(1 === sizeof($courses));
        $this->assertTrue(1 == $courses[0]['isDefault']);
    }

    public function testCreateLiveOpen()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'liveOpen',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertTrue($created['id'] > 0);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($created['id']);
        $this->assertTrue(1 === sizeof($courses));
        $this->assertTrue(1 == $courses[0]['isDefault']);
    }

    public function testCreateOpen()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'open',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertTrue($created['id'] > 0);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($created['id']);
        $this->assertTrue(1 === sizeof($courses));
        $this->assertTrue(1 == $courses[0]['isDefault']);
    }

    /**
     * @expectedException  \Biz\Common\CommonException
     */
    public function testCreateError()
    {
        $courseSet = array(
            'title' => '新课程开始！',
        );
        $this->getCourseSetService()->createCourseSet($courseSet);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testRecommendCourseException()
    {
        $courseSet = array(
            'title' => '新课程',
            'type' => 'normal',
        );

        $this->getCourseSetService()->createCourseSet($courseSet);
        $this->getCourseSetService()->recommendCourse(1, 'a');
    }

    private function createAndPublishCourseSet($title, $type)
    {
        $courseSet = array(
            'title' => $title,
            'type' => $type,
        );

        $courseSet = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->getCourseSetService()->publishCourseSet($courseSet['id']);

        return $this->getCourseSetService()->getCourseSet($courseSet['id']);
    }

    public function testRecommendCourse()
    {
        $this->createAndPublishCourseSet('新课程哇', 'normal');
        $number = 0;

        $recommendCourseSet = $this->getCourseSetService()->recommendCourse(1, $number);

        $excepted = array(
            'id' => 1,
            'recommended' => 1,
            'recommendedSeq' => (int)$number,
            'recommendedTime' => time(),
        );

        $this->assertArraySternEquals($excepted, $recommendCourseSet);
    }

    public function testCancelRecommendCourse()
    {
        $courseSet = array(
            'title' => '新课程哇',
            'type' => 'normal',
        );

        $this->getCourseSetService()->createCourseSet($courseSet);
        $this->getCourseSetService()->recommendCourse(1, 20);
        $this->getCourseSetService()->cancelRecommendCourse(1);
        $courseSet = $this->getCourseSetService()->getCourseSet(1);

        $excepted = array(
            'recommended' => 0,
            'recommendedTime' => 0,
            'recommendedSeq' => 0,
        );

        $this->assertArraySternEquals($excepted, $courseSet);
    }

    public function testFindRandomCourseSets()
    {
        $courseSet1 = $this->createAndPublishCourseSet('新课程1', 'normal');
        sleep(1);
        $courseSet2 = $this->createAndPublishCourseSet('新课程2', 'normal');
        sleep(1);
        $courseSet3 = $this->createAndPublishCourseSet('新课程3', 'open');
        sleep(1);
        $this->getCourseSetService()->createCourseSet(array('title' => '新课程4', 'type' => 'normal'));
        sleep(1);
        $this->createAndPublishCourseSet('新课程5', 'open');
        $this->getCourseSetService()->recommendCourse(5, 2321);
        $courseSet5 = $this->getCourseSetService()->getCourseSet(5);

        $conditionsA = array(
            'status' => 'published',
            'recommended' => 1,
            'parentId' => 0,
        );

        $conditionsB = array(
            'status' => 'published',
            'parentId' => 0,
        );

        $courseSetsA = $this->getCourseSetService()->findRandomCourseSets($conditionsA, 5);

        $this->assertEquals(1, $this->count($courseSetsA));
        $this->assertArraySternEquals($courseSetsA[0], $courseSet5);

        $courseSetsB = $this->getCourseSetService()->findRandomCourseSets($conditionsB, 5);
        $expected = array($courseSet5, $courseSet3, $courseSet2, $courseSet1);

        $this->assertArraySternEquals($expected, $courseSetsB);
    }

    public function testFavorite()
    {
        $courseSet = $this->createAndPublishCourseSet('测试课程', 'normal');

        $result = $this->getCourseSetService()->favorite($courseSet['id']);

        $this->assertTrue($result);

        $result = $this->getCourseSetService()->favorite(2);
        $this->assertFalse($result);
    }

    public function testUnfavorite()
    {
        $courseSet = $this->createAndPublishCourseSet('测试课程', 'normal');
        $this->getCourseSetService()->favorite($courseSet['id']);

        $result = $this->getCourseSetService()->unfavorite(3);
        $this->assertFalse($result);

        $result = $this->getCourseSetService()->unfavorite(1);
        $this->assertTrue($result);

        $result = $this->getCourseSetService()->favorite(1);
        $this->assertTrue($result);

        $result = $this->getCourseSetService()->unfavorite(1);
        $this->assertTrue($result);
    }

    public function testIsUserFavorite()
    {
        $courseSet = $this->createAndPublishCourseSet('课程', 'normal');
        $this->getCourseSetService()->favorite($courseSet['id']);
        $result = $this->getCourseSetService()->favorite($courseSet['id']);
        $this->assertTrue($result);
    }

    /**
     * @expectedException \Biz\Course\CourseSetException
     * @expectedExceptionMessage exception.courseset.not_found
     */
    public function testIsUserFavoriteException()
    {
        $this->getCourseSetService()->isUserFavorite(1, 1);
    }

    public function testHasCourseSetManageRoleUnLogin()
    {
        $this->createAndPublishCourseSet('课程1', 'normal');

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1),
        ));

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->assertFalse($this->getCourseSetService()->hasCourseSetManageRole(1));
    }

    public function testHasCourseSetManageRoleFalse()
    {
        $this->createAndPublishCourseSet('课程1','normal');

        $user = $this->getUserService()->register(array(
            'nickname' => 'user',
            'email' => 'user@user.com',
            'password' => 'user',
            'createdIp' => '127.0.0.1',
            'orgCode' => '1.',
            'orgId' => '1',
        ));

        $user['currentIp'] = $user['createdIp'];
        $user['org'] = array('id' => 1);
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->grantPermissionToUser($currentUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        var_dump($this->getCurrentUser()->isAdmin());
        var_dump($this->getCurrentUser()->getRoles());
        var_dump($this->getCourseSetService()->hasCourseSetManageRole(0));
        $this->assertFalse();
        $this->assertFalse($this->getCourseSetService()->hasCourseSetManageRole(3231));
        $this->assertFalse($this->getCourseSetService()->hasCourseSetManageRole(1));
    }

    public function testHasCourseSetManageRole()
    {
        $this->createAndPublishCourseSet('课程1', 'normal');

        $result = $this->getCourseSetService()->hasCourseSetManageRole();
        $this->assertTrue($result);
        $result = $this->getCourseSetService()->hasCourseSetManageRole(2332);
        $this->assertTrue($result);
        $courseSet = $this->createAndPublishCourseSet('课程', 'normal');
        $result = $this->getCourseSetService()->hasCourseSetManageRole($courseSet['id']);
        $this->assertTrue($result);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.unlogin
     */
    public function testTryManageCourseSetUnLogin()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1),
        ));

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getCourseSetService()->tryManageCourseSet(1);
    }

    /**
     * @expectedException \Biz\Course\CourseSetException
     * @expectedExceptionMessage exception.courseset.not_found
     */
    public function testTryManageCourseSetNotFoundException()
    {
        $this->getCourseSetService()->tryManageCourseSet(1);
    }

    public function testCountCourseSets()
    {
        $this->createAndPublishCourseSet('新课程1', 'normal');
        $this->createAndPublishCourseSet('新课程2', 'normal');
        $this->createAndPublishCourseSet('新课程3', 'open');
        $this->getCourseSetService()->createCourseSet(array('title' => '新课程4', 'type' => 'normal'));
        $this->createAndPublishCourseSet('新课程5', 'open');
        $this->getCourseSetService()->recommendCourse(5, 2321);
        $this->getCourseSetService()->getCourseSet(5);

        $conditions = array('type' => 'normal', 'status' => 'published');
        $count = $this->getCourseSetService()->countCourseSets($conditions);
        $this->assertEquals(2, $count);

        $conditions = array();
        $count = $this->getCourseSetService()->countCourseSets($conditions);
        $this->assertEquals(5, $count);
    }

    public function testFindCourseSetsLikeTitle()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $expected = $this->getCourseSetService()->createCourseSet($courseSet);
        $res = $this->getCourseSetService()->findCourseSetsLikeTitle('开始');

        $this->assertEquals($expected['title'], $res[0]['title']);
    }

    public function testUpdate()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);

        $created['title'] = '新课程开始(更新)！';
        $created['subtitle'] = '新课程的副标题参见！';
        $created['tags'] = 'new';
        $created['categoryId'] = 6;
        $created['serializeMode'] = 'none';
        $updated = $this->getCourseSetService()->updateCourseSet($created['id'], $created);
        $this->assertEquals($created['title'], $updated['title']);
    }

    public function testChangeCover()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $updated = $this->getCourseSetService()->changeCourseSetCover($created['id'], array(
            'large' => 1,
            'middle' => 2,
            'small' => 3,
        ));
        $this->assertNotEmpty($updated['cover']);
    }

    public function testDelete()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertNotEmpty($created);
        $this->getCourseSetService()->deleteCourseSet($created['id']);
        $deleted = $this->getCourseSetService()->getCourseSet($created['id']);
        $this->assertEmpty($deleted);
    }

    public function testUpdateCourseSetStatistics()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertNotEmpty($created);

        $result = $this->getCourseSetService()->updateCourseSetStatistics($created['id'], array('ratingNum'));

        $this->assertEquals(0, $result['ratingNum']);
    }

    private function initTags()
    {
        $tagA = array();
        $tagA['name'] = 'TagA';
        $tagA = $this->getTagService()->addTag($tagA);

        $tagB = array();
        $tagB['name'] = 'TagB';
        $tagB = $this->getTagService()->addTag($tagB);

        $tagC = array();
        $tagC['name'] = 'TagC';
        $tagC = $this->getTagService()->addTag($tagC);

        return array($tagA, $tagB, $tagC);
    }

    public function testRelatedCourseSet()
    {
        list($tagA, $tagB, $tagC) = $this->initTags();

        $courseSetA = array(
            'title' => 'TagABC',
            'type' => 'normal',
        );
        $createdA = $this->getCourseSetService()->createCourseSet($courseSetA);
        $createdA['tags'] = $tagA['name'] . ',' . $tagB['name'] . ',' . $tagC['name'];
        $createdA = $this->getCourseSetService()->updateCourseSet($createdA['id'], $createdA);

        $courseSetB = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $createdB = $this->getCourseSetService()->createCourseSet($courseSetB);
        $this->getCourseSetService()->publishCourseSet($createdB['id']);
        $createdB['tags'] = $tagB['name'] . ',' . $tagC['name'];
        $createdB = $this->getCourseSetService()->updateCourseSet($createdB['id'], $createdB);

        $courseSetC = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $createdC = $this->getCourseSetService()->createCourseSet($courseSetC);
        $this->getCourseSetService()->publishCourseSet($createdC['id']);

        $createdC['tags'] = $tagC['name'] . ',' . $tagA['name'] . ',' . $tagB['name'];
        $createdC = $this->getCourseSetService()->updateCourseSet($createdC['id'], $createdC);

        $courseSetD = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $createdD = $this->getCourseSetService()->createCourseSet($courseSetD);
        $this->getCourseSetService()->publishCourseSet($createdD['id']);
        $createdD['tags'] = $tagA['name'];
        $createdD = $this->getCourseSetService()->updateCourseSet($createdD['id'], $createdD);

        $relatedCourseSets = $this->getCourseSetService()->findRelatedCourseSetsByCourseSetId($createdA['id'], 4);
        $this->assertArraySternEquals($relatedCourseSets, array($createdC, $createdB, $createdD));
    }

    public function testRelatedCourseSetNoTags()
    {
        $courseSetA = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $createdA = $this->getCourseSetService()->createCourseSet($courseSetA);
        $courseSetB = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $createdB = $this->getCourseSetService()->createCourseSet($courseSetB);
        $relatedCourseSets = $this->getCourseSetService()->findRelatedCourseSetsByCourseSetId($createdA['id'], 4);
        $this->assertEmpty($relatedCourseSets);
    }

    public function testRefreshHotSeq()
    {
        $fields = array(
            'title' => '新课程开始！',
            'type' => 'normal',
            'hotSeq' => 10,
        );
        $courseSet = $this->getCourseSetDao()->create($fields);
        $this->assertEquals($fields['hotSeq'], $courseSet['hotSeq']);

        $this->getCourseSetService()->refreshHotSeq();

        $result = $this->getCourseSetService()->getCourseSet($courseSet['id']);

        $this->assertEquals(0, $result['hotSeq']);
    }

    public function testUpdateCourseSummary()
    {
        $courseSet = array(
            'title' => 'courseSetTitle',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSet);
        $courseFields = array(
            'id' => 2,
            'courseSetId' => $courseSet['id'],
            'title' => '计划名称',
            'learnMode' => 'freeMode',
            'expiryDays' => 0,
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        );
        $course = $this->getCourseService()->createCourse($courseFields);
        $firstCourse = $this->getCourseService()->updateCourse(1, array('summary' => '计划简介1'));
        $secondCourse = $this->getCourseService()->updateCourse(2, array('summary' => '计划简介2'));

        ReflectionUtils::invokeMethod($this->getCourseSetService(), 'updateCourseSummary', array($courseSet));
        $result = $this->getCourseService()->getCourse($firstCourse['id']);
        $this->assertEmpty($result['summary']);

        $result = $this->getCourseService()->getCourse($secondCourse['id']);
        $this->assertEmpty($result['summary']);
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getDiscountService()
    {
        return $this->createService('DiscountPlugin:Discount:DiscountService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
