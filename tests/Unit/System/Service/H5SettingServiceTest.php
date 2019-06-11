<?php

namespace Tests\Unit\System\Service;

use Biz\System\Service\H5SettingService;
use Biz\BaseTestCase;

class H5SettingServiceTest extends BaseTestCase
{
    public function testGetDiscovery()
    {
        $this->mockBiz('Content:BlockService', array(
            array('functionName' => 'getPosters', 'returnValue' => array(0 => array('image' => '', 'link' => array('url' => '')))),
        ));
        $discoverySettings = $this->getH5SettingService()->getDiscovery('h5');
        $this->assertEquals('slide_show', $discoverySettings['slide-1']['type']);
        $this->assertEquals('course_list', $discoverySettings['courseList-1']['type']);
    }

    public function testFilter()
    {
        $discoverySettings = $this->createFilter();

        $discoverySettings = $this->getH5SettingService()->filter($discoverySettings);
        $this->assertEquals('slide_show', $discoverySettings['slide-1']['type']);
    }

    public function testCourseListFilter()
    {
        $discoverySetting = $this->createTypeListByCondition('course_list');
        $discoverySetting = $this->getH5SettingService()->courseListFilter($discoverySetting);

        $this->assertEquals('course_list', $discoverySetting['type']);
        $this->assertEquals('condition', $discoverySetting['data']['sourceType']);

        $this->mockCourse();

        $discoverySetting = $this->createTypeListByCustom('course_list');
        $discoverySetting = $this->getH5SettingService()->courseListFilter($discoverySetting);
        $this->assertEmpty($discoverySetting['data']['items']);
        $this->assertEquals('custom', $discoverySetting['data']['sourceType']);
    }

    public function testClassroomListFilter()
    {
        $discoverySetting = $this->createTypeListByCondition('classroom_list');
        $discoverySetting = $this->getH5SettingService()->classroomListFilter($discoverySetting);

        $this->assertEquals('classroom_list', $discoverySetting['type']);
        $this->assertEquals('condition', $discoverySetting['data']['sourceType']);

        $this->mockClassroom();

        $discoverySetting = $this->createTypeListByCustom('classroom_list');
        $discoverySetting = $this->getH5SettingService()->classroomListFilter($discoverySetting);
        $this->assertEmpty($discoverySetting['data']['items']);
        $this->assertEquals('custom', $discoverySetting['data']['sourceType']);
    }

    public function testSlideShowFilter()
    {
        $discoverySetting = $this->createSlide();

        $discoverySetting = $this->getH5SettingService()->slideShowFilter($discoverySetting);
        $this->assertEquals('slide_show', $discoverySetting['type']);
        $this->assertNull($discoverySetting['data'][0]['link']['target']);
        $this->assertEmpty($discoverySetting['data'][0]['link']['url']);
    }

    public function testPosterFilter()
    {
        $discoverySetting = $this->createPoster();

        $discoverySetting = $this->getH5SettingService()->posterFilter($discoverySetting);
        $this->assertEquals('poster', $discoverySetting['type']);
        $this->assertNull($discoverySetting['data']['link']['target']);
        $this->assertEmpty($discoverySetting['data']['link']['url']);
    }

    public function testGrouponFilter()
    {
        $discoverySetting = $this->createEmptyActivity();
        $discoverySetting = $this->getH5SettingService()->grouponFilter($discoverySetting);
        $this->assertFalse($discoverySetting);

        $discoverySetting = $this->createActivity();
        $this->mockBiz('Marketing:MarketingPlatformService', array(
            array('functionName' => 'getActivity', 'returnValue' => array('id' => 1)),
        ));
        $discoverySetting = $this->getH5SettingService()->grouponFilter($discoverySetting);
        $this->assertEquals(1, $discoverySetting['data']['activity']['id']);
    }

    public function testSeckillFilter()
    {
        $discoverySetting = $this->createEmptyActivity();
        $discoverySetting = $this->getH5SettingService()->seckillFilter($discoverySetting);
        $this->assertFalse($discoverySetting);

        $discoverySetting = $this->createActivity();
        $this->mockBiz('Marketing:MarketingPlatformService', array(
            array('functionName' => 'getActivity', 'returnValue' => array('id' => 1)),
        ));
        $discoverySetting = $this->getH5SettingService()->seckillFilter($discoverySetting);
        $this->assertEquals(1, $discoverySetting['data']['activity']['id']);
    }

    public function testCutFilter()
    {
        $discoverySetting = $this->createEmptyActivity();
        $discoverySetting = $this->getH5SettingService()->cutFilter($discoverySetting);
        $this->assertFalse($discoverySetting);

        $discoverySetting = $this->createActivity();
        $this->mockBiz('Marketing:MarketingPlatformService', array(
            array('functionName' => 'getActivity', 'returnValue' => array('id' => 1)),
        ));
        $discoverySetting = $this->getH5SettingService()->cutFilter($discoverySetting);
        $this->assertEquals(1, $discoverySetting['data']['activity']['id']);
    }

    public function testCouponFilter()
    {
        $this->mockCouponService();
        $discoverySetting = $this->createCoupon();
        $discoverySetting = $this->getH5SettingService()->couponFilter($discoverySetting);

        $this->assertEquals(0, $discoverySetting['data']['items'][0]['money']);
        $this->assertEquals(1, $discoverySetting['data']['items'][0]['usedNum']);
        $this->assertEquals(1, $discoverySetting['data']['items'][0]['unreceivedNum']);
    }

    public function testVipFilter()
    {
        $this->mockVipService();

        $discoverySetting = $this->createVip();
        $discoverySetting = $this->getH5SettingService()->vipFilter($discoverySetting);

        $this->assertEquals(1, $discoverySetting['data']['items'][0]['freeCourseNum']);
        $this->assertEquals(1, $discoverySetting['data']['items'][0]['freeClassroomNum']);
    }

    public function testGetCourseCondition()
    {
        $this->mockBiz('Taxonomy:CategoryService', array(
            array('functionName' => 'getGroupByCode', 'returnValue' => array('id' => 1)),
            array('functionName' => 'findCategoriesByGroupIdAndParentId', 'returnValue' => array('id' => 1, 'code' => 'test')),
        ));

        $conditions = $this->getH5SettingService()->getCourseCondition('h5');
        $this->assertEquals('所有课程', $conditions['title']);
    }

    protected function createFilter()
    {
        return array(
            'slide-1' => array(
                'type' => 'slide_show',
                'moduleType' => 'slide-1',
                'data' => array(
                    0 => array(
                        'title' => '',
                        'image' => array(
                            'id' => 0,
                            'uri' => '',
                            'size' => '',
                            'createdTime' => 0,
                        ),
                        'link' => array(
                            'type' => 'url',
                            'target' => null,
                            'url' => '',
                        ),
                    ),
                ),
            ),
        );
    }

    protected function createTypeListByCondition($type)
    {
        return array(
            'type' => $type,
            'data' => array(
                'sourceType' => 'condition',
                'categoryId' => 0,
                'sort' => '-studentNum',
                'lastDays' => 0,
                'limit' => 1,
                'items' => array(),
            ),
        );
    }

    protected function createTypeListByCustom($type)
    {
        return array(
            'type' => $type,
            'data' => array(
                'sourceType' => 'custom',
                'items' => array(
                    0 => array('id' => 1),
                    1 => array('id' => 2),
                    2 => array('id' => 3),
                ),
            ),
        );
    }

    protected function createSlide()
    {
        return array(
            'type' => 'slide_show',
            'data' => array(
                0 => array(
                    'link' => array(
                        'type' => '',
                        'target' => null,
                        'url' => '',
                    ),
                ),
            ),
        );
    }

    protected function createPoster()
    {
        return array(
            'type' => 'poster',
            'data' => array(
                'link' => array(
                    'type' => '',
                    'target' => null,
                    'url' => '',
                ),
            ),
        );
    }

    protected function createEmptyActivity()
    {
        return array(
            'data' => array(
                'activity' => array(),
            ),
        );
    }

    protected function createActivity()
    {
        return array(
            'data' => array(
                'activity' => array(
                    'id' => 0,
                ),
            ),
        );
    }

    protected function createCoupon()
    {
        return array(
            'data' => array(
                'sort' => 'desc',
                'items' => array(
                    0 => array('id' => 1),
                    1 => array('id' => 2),
                ),
            ),
        );
    }

    protected function createVip()
    {
        return array(
            'data' => array(
                'sort' => 'desc',
                'items' => array(),
            ),
        );
    }

    protected function mockCourse()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->defaultCourse('course title 1', $courseSet);
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);

        $course = $this->defaultCourse('course title 2', $courseSet);
        $createCourse = $this->getCourseService()->createCourse($course);
    }

    protected function mockClassroom()
    {
        $textClassroom = array(
            'title' => 'test001',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
    }

    protected function createNewCourseSet()
    {
        $courseSetFields = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);

        $this->assertNotEmpty($courseSet);

        return $courseSet;
    }

    protected function defaultCourse($title, $courseSet, $isDefault = 1)
    {
        return array(
            'title' => $title,
            'courseSetId' => $courseSet['id'],
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'isDefault' => $isDefault,
            'courseType' => $isDefault ? 'default' : 'normal',
        );
    }

    protected function createNewCourse($courseSetId)
    {
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(array($courseSetId));

        if (empty($courses)) {
            $courseFields = array(
                'title' => '第一个教学计划',
                'courseSetId' => 1,
                'learnMode' => 'lockMode',
                'expiryDays' => 0,
                'expiryMode' => 'forever',
            );

            $course = $this->getCourseService()->createCourse($courseFields);
        } else {
            $course = $courses[0];
        }

        $this->assertNotEmpty($course);

        return $course;
    }

    protected function mockCouponService()
    {
        $this->mockBiz('CloudPlatform:AppService', array(
            array('functionName' => 'getAppByCode', 'returnValue' => array('type' => 'plugin')),
        ));
        $this->mockBiz('CouponPlugin:Coupon:CouponBatchService', array(
            array('functionName' => 'fillUserCurrentCouponByBatches', 'returnValue' => array(
                0 => array('id' => 1),
                1 => array('id' => 2),
                2 => array('id' => 3),
            )),
            array('functionName' => 'findBatchsByIds', 'returnValue' => array(
                1 => array('deadline' => time(), 'money' => 0, 'usedNum' => 1, 'unreceivedNum' => 1, 'targetType' => 'vip', 'targetId' => 1),
                2 => array('deadline' => time() - 100000, 'money' => 0, 'usedNum' => 1, 'unreceivedNum' => 1,  'targetType' => 'vip', 'targetId' => 1),
            )),
        ));
        $this->mockBiz('VipPlugin:Vip:LevelService', array(
            array('functionName' => 'getLevel', 'returnValue' => array('id' => 1)),
        ));
    }

    protected function mockVipService()
    {
        $this->mockBiz('CloudPlatform:AppService', array(
            array('functionName' => 'getAppByCode', 'returnValue' => array('type' => 'plugin')),
        ));
        $this->mockBiz('VipPlugin:Vip:LevelService', array(
            array('functionName' => 'findEnabledLevels', 'returnValue' => array(0 => array('id' => 1))),
            array('functionName' => 'getFreeCourseNumByLevelId', 'returnValue' => 1),
            array('functionName' => 'getFreeClassroomNumByLevelId', 'returnValue' => 1),
        ));
    }

    private function getH5SettingService()
    {
        return $this->createService('System:H5SettingService');
    }

    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
