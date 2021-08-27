<?php

namespace Tests\Unit\TeacherQualification\Service;

use Biz\BaseTestCase;
use Biz\TeacherQualification\Dao\TeacherQualificationDao;
use Biz\TeacherQualification\Service\TeacherQualificationService;
use Biz\User\Service\UserService;

class TeacherQualificationServiceTest extends BaseTestCase
{
    public function testGetByUserId()
    {
        $this->mockTeacherQualification();
        $qualification = $this->getTeacherQualificationService()->getByUserId(1);
        $this->assertEquals(1, $qualification['user_id']);
    }

    public function testFindByUserIds()
    {
        $this->mockTeacherQualification();
        $this->mockTeacherQualification(
            ['user_id' => 2]
        );
        $qualifications = $this->getTeacherQualificationService()->findByUserIds([1, 2]);
        $this->assertCount(2, $qualifications);
    }

    public function testSearch()
    {
        $this->mockTeacherQualification();
        $qualifications = $this->getTeacherQualificationService()->search(['user_id' => 1], [], 0, PHP_INT_MAX);
        $this->assertCount(1, $qualifications);
    }

    public function testCount()
    {
        $this->mockTeacherQualification();
        $qualificationCount = $this->getTeacherQualificationService()->count(['user_id' => 1]);
        $this->assertEquals(1, $qualificationCount);
    }

    public function testCountTeacherQualification()
    {
        $user = $this->mockUser();
        $this->mockTeacherQualification(['user_id' => $user['id']]);
        $qualificationCount = $this->getTeacherQualificationService()->countTeacherQualification(['user_id' => $user['id']]);
        $this->assertEquals(1, $qualificationCount);
    }

    public function testSearchTeacherQualification()
    {
        $user = $this->mockUser();
        $this->mockTeacherQualification(['user_id' => $user['id']]);
        $qualifications = $this->getTeacherQualificationService()->searchTeacherQualification(['user_id' => $user['id']], [], 0, PHP_INT_MAX);
        $this->assertCount(1, $qualifications);
    }

    public function testChangeQualification()
    {
        $params = [
            [
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => [
                    'id' => 1,
                    'uri' => '',
                ],
            ],
        ];
        $this->mockBiz('Content:FileService', $params);

        $fields = [
            'userId' => 11,
            'code' => 123456789101112,
        ];

        $qualification = $this->getTeacherQualificationService()->changeQualification(11, $fields);
        $this->assertEquals(11, $qualification['user_id']);
    }

    protected function mockTeacherQualification($fields = [])
    {
        $qualificationFields = array_merge([
            'user_id' => 1,
            'avatar' => '',
            'avatarFileId' => 1,
            'code' => 123456789101112,
        ], $fields);

        return $this->getTeacherQualificationDao()->create($qualificationFields);
    }

    protected function mockUser($userFields = [])
    {
        $userInfo = array_merge([
            'nickname' => 'test_nickname',
            'password' => 'test_password',
            'email' => 'test_email@email.com',
            'verifiedMobile' => '13967340620',
            'mobile' => '13967340621',
        ], $userFields);

        return $this->getUserService()->register($userInfo);
    }

    /**
     * @return TeacherQualificationDao
     */
    protected function getTeacherQualificationDao()
    {
        return $this->createDao('TeacherQualification:TeacherQualificationDao');
    }

    /**
     * @return TeacherQualificationService
     */
    protected function getTeacherQualificationService()
    {
        return $this->createService('TeacherQualification:TeacherQualificationService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
