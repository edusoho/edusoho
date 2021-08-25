<?php

namespace Tests\Unit\TeacherQualification\Dao;

use Biz\BaseTestCase;
use Biz\TeacherQualification\Dao\TeacherQualificationDao;
use Biz\User\Service\UserService;

class TeacherQualificationDaoTest extends BaseTestCase
{
    public function testGetByUserId()
    {
        $this->mockTeacherQualification();
        $qualification = $this->getTeacherQualificationDao()->getByUserId(1);
        $this->assertEquals(1, $qualification['user_id']);
    }

    public function testFindByUserIds()
    {
        $this->mockTeacherQualification();
        $this->mockTeacherQualification(
            ['user_id' => 2]
        );
        $qualifications = $this->getTeacherQualificationDao()->findByUserIds([1, 2]);
        $this->assertCount(2, $qualifications);
    }

    public function testCountTeacherQualification()
    {
        $user = $this->mockUser();
        $this->mockTeacherQualification(['user_id' => $user['id']]);
        $qualificationCount = $this->getTeacherQualificationDao()->countTeacherQualification(['user_id' => $user['id']]);
        $this->assertEquals(1, $qualificationCount);
    }

    public function testSearchTeacherQualification()
    {
        $user = $this->mockUser();
        $this->mockTeacherQualification(['user_id' => $user['id']]);
        $qualifications = $this->getTeacherQualificationDao()->searchTeacherQualification(['user_id' => $user['id']], [], 0, PHP_INT_MAX);
        $this->assertCount(1, $qualifications);
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
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
