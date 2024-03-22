<?php

namespace Tests\Unit\Importer;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Importer\ClassroomMemberImporter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ClassroomMemberImporterTest extends BaseTestCase
{
    public function testImport()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $request = new Request(
            [],
            [
                'importData' => [['id' => 5, 'nickname' => '', 'email' => '', 'verifiedMobile' => 13845789587]],
                'classroomId' => 111,
                'price' => 10,
                'remark' => '通过批量导入添加',
            ]
        );
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'getArticle',
                    'returnValue' => ['id' => 111, 'userId' => 3],
                    'withParams' => [111],
                ],
                [
                    'functionName' => 'getUserByVerifiedMobile',
                    'returnValue' => ['id' => 5, 'nickname' => 'nickname'],
                    'withParams' => [13845789587],
                ],
            ]
        );
        $this->mockBiz(
            'Classroom:ClassroomService',
            [
                [
                    'functionName' => 'getClassroom',
                    'returnValue' => ['id' => 111, 'title' => 'title'],
                    'withParams' => [111],
                ],
                [
                    'functionName' => 'isClassroomStudent',
                    'returnValue' => true,
                    'withParams' => [111, 5],
                ],
                [
                    'functionName' => 'isClassroomTeacher',
                    'returnValue' => false,
                    'withParams' => [111, 5],
                ],
            ]
        );
        $result = $importer->import($request);
        $this->assertEquals(1, $result['existsUserCount']);
    }

    public function testExcelDataImportingWithNotEmptyNickname()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $targetObject = ['id' => 111];
        $userData = [['nickname' => 'nickname']];
        $orderData = ['amount' => 10, 'remark' => '通过批量导入添加'];
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'getUserByNickname',
                    'returnValue' => ['id' => 5, 'nickname' => 'nickname'],
                    'withParams' => ['nickname'],
                ],
            ]
        );
        $this->mockBiz(
            'Classroom:ClassroomService',
            [
                [
                    'functionName' => 'isClassroomStudent',
                    'returnValue' => false,
                    'withParams' => [111, 5],
                ],
                [
                    'functionName' => 'isClassroomTeacher',
                    'returnValue' => false,
                    'withParams' => [111, 5],
                ],
                [
                    'functionName' => 'becomeStudentWithOrder',
                    'withParams' => [111, 5, ['price' => 10, 'remark' => '通过批量导入添加', 'isNotify' => 1]],
                ],
            ]
        );
        $result = ReflectionUtils::invokeMethod($importer, 'excelDataImporting', [$targetObject, $userData, $orderData]);
        $this->getClassroomService()->shouldHaveReceived('becomeStudentWithOrder');
        $this->assertEquals(1, $result['successCount']);
    }

    public function testExcelDataImportingWithNotEmptyEmail()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $targetObject = ['id' => 111];
        $userData = [['nickname' => '', 'email' => '1@qq.com']];
        $orderData = ['amount' => 10, 'remark' => '通过批量导入添加'];
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'getUserByEmail',
                    'returnValue' => ['id' => 5, 'email' => '1@qq.com'],
                    'withParams' => ['1@qq.com'],
                ],
            ]
        );
        $this->mockBiz(
            'Classroom:ClassroomService',
            [
                [
                    'functionName' => 'isClassroomStudent',
                    'returnValue' => false,
                    'withParams' => [111, 5],
                ],
                [
                    'functionName' => 'isClassroomTeacher',
                    'returnValue' => true,
                    'withParams' => [111, 5],
                ],
            ]
        );
        $result = ReflectionUtils::invokeMethod($importer, 'excelDataImporting', [$targetObject, $userData, $orderData]);
        $this->assertEquals(1, $result['existsUserCount']);
    }

    public function testCheck()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $request = new Request(
            [],
            [
                'classroomId' => 111,
                'price' => 10,
                'remark' => '通过批量导入添加',
            ],
            [],
            [],
            [
                'excel' => new UploadedFile(__DIR__.'/File/classmember_import.xls', 'classmember_import.xls'),
            ]
        );
        $user = ['email' => 'test@qq.com', 'password' => 'password', 'salt' => 'salt', 'nickname' => 'test', 'type' => 'default', 'roles' => ['ROLE_USER']];
        $member = $this->getUserDao()->create($user);
        $result = $importer->check($request);
        $this->assertEquals('success', $result['status']);
    }

    public function testCheckWithNotEmptyDanger()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $request = new Request(
            [],
            [
                'classroomId' => 111,
                'price' => 10,
                'remark' => '通过批量导入添加',
            ],
            [],
            [],
            [
                'excel' => [],
            ]
        );
        $result = $importer->check($request);
        $this->assertEquals('danger', $result['status']);
    }

    public function testCheckWithNotEmptyErrorInfo()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $request = new Request(
            [],
            [
                'classroomId' => 111,
                'price' => 10,
                'remark' => '通过批量导入添加',
            ],
            [],
            [],
            [
                'excel' => new UploadedFile(__DIR__.'/File/classmember_import.xls', 'classmember_import.xls'),
            ]
        );
        $result = $importer->check($request);
        $this->assertEquals('error', $result['status']);
    }

    public function testCheckPassedRepeatData()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        ReflectionUtils::setProperty($importer, 'passValidateUser', [['id' => 11, 'row' => 'test'], ['id' => 11, 'row' => 'test']]);
        $result = ReflectionUtils::invokeMethod($importer, 'checkPassedRepeatData');
        $this->assertNotNull($result[0]);
    }

    public function testCalculateChunkNum()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $result = ReflectionUtils::invokeMethod($importer, 'calculateChunkNum');
        $this->assertEquals(8, $result);
    }

    public function testGetUserData()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        ReflectionUtils::invokeMethod($importer, 'excelAnalyse', [new UploadedFile(__DIR__.'/File/classmember_import.xls', 'classmember_import.xls')]);
        $result = ReflectionUtils::invokeMethod($importer, 'getUserData');
        $this->assertEquals(1, $result['userCount']);
    }

    public function testValidateExcelFile()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $result = ReflectionUtils::invokeMethod($importer, 'validateExcelFile', [new UploadedFile(__DIR__.'/File/classmember_import.xls', 'classmember_import.xls')]);
        $this->assertNull($result);
    }

    public function testValidateExcelFileWithNotFile()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $result = ReflectionUtils::invokeMethod($importer, 'validateExcelFile', [[]]);
        $this->assertEquals('请选择上传的文件', $result['message']);
    }

    public function testValidateExcelFileWithoutNecessaryFields()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $result = ReflectionUtils::invokeMethod($importer, 'validateExcelFile', [new UploadedFile(__DIR__.'/File/wrong_classmember_import.xls', 'wrong_classmember_import.xls')]);

        $this->assertEquals('缺少必要的字段', $result['message']);
    }

    public function testValidExcelFieldValue()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $userData = ['nickname' => 'testname', 'email' => 'test@qq.com', 'verifiedMobile' => 13758746895];
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'getUserByNickname',
                    'returnValue' => ['id' => 5, 'email' => 'test@qq.com', 'nickname' => 'testname', 'verifiedMobile' => 13758746895],
                    'withParams' => ['testname'],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'getUserByNickname',
                    'returnValue' => ['id' => 5, 'email' => 'test@qq.com', 'nickname' => 'nickname', 'verifiedMobile' => 13758746895],
                    'withParams' => ['testname'],
                    'runTimes' => 1,
                ],
            ]
        );
        $result = ReflectionUtils::invokeMethod($importer, 'validExcelFieldValue', [$userData, 2, 0]);
        $this->assertEquals('', $result);

        $result = ReflectionUtils::invokeMethod($importer, 'validExcelFieldValue', [$userData, 3, 0]);
        $this->assertEquals('第3行的信息有误，用户数据不存在，请检查。', $result);
    }

    public function testValidExcelFieldValueWithNotEmptyEmail()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $userData = ['nickname' => '', 'email' => 'test@qq.com', 'verifiedMobile' => 13758746895];
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'getUserByEmail',
                    'returnValue' => ['id' => 5, 'email' => 'qq@qq.com', 'nickname' => 'test'],
                    'withParams' => ['test@qq.com'],
                ],
            ]
        );
        $result = ReflectionUtils::invokeMethod($importer, 'validExcelFieldValue', [$userData, 2, 0]);
        $this->assertEquals('第2行的信息有误，用户数据不存在，请检查。', $result);
    }

    public function testValidExcelFieldValueWithNotEmptyVerifiedMobile()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $userData = ['nickname' => '', 'email' => '', 'verifiedMobile' => 13758746895];
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'getUserByVerifiedMobile',
                    'returnValue' => ['id' => 5, 'email' => 'qq@qq.com', 'nickname' => 'test', 'verifiedMobile' => 13766665555],
                    'withParams' => [13758746895],
                ],
            ]
        );
        $result = ReflectionUtils::invokeMethod($importer, 'validExcelFieldValue', [$userData, 2, 0]);
        $this->assertEquals('第2行的信息有误，用户数据不存在，请检查。', $result);
    }

    public function testCheckRepeatData()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        ReflectionUtils::invokeMethod($importer, 'excelAnalyse', [new UploadedFile(__DIR__.'/File/classmember_import.xls', 'classmember_import.xls')]);
        $result = ReflectionUtils::invokeMethod($importer, 'checkRepeatData');
        $this->assertEquals([], $result);
    }

    public function testArrayRepeat()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $repeatArray = ['test', 'test', '', '', ''];
        $result = ReflectionUtils::invokeMethod($importer, 'arrayRepeat', [$repeatArray, 2]);
        $this->assertEquals('第3列重复，重复内容如下:<br>第3行：test<br>第4行：test<br>', $result);
    }

    public function testGetFieldSort()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        ReflectionUtils::invokeMethod($importer, 'excelAnalyse', [new UploadedFile(__DIR__.'/File/classmember_import.xls', 'classmember_import.xls')]);
        $result = ReflectionUtils::invokeMethod($importer, 'getFieldSort');
        $this->assertEquals(0, $result['nickname']['num']);
    }

    public function testExcelAnalyse()
    {
        $expect = ['用户名', '邮箱', '手机'];
        $importer = new ClassroomMemberImporter($this->getBiz());
        $result = ReflectionUtils::invokeMethod(
            $importer,
            'excelAnalyse',
            [new UploadedFile(__DIR__.'/File/classmember_import.xls', 'classmember_import.xls')]
        );
        $this->assertEquals($expect, $result[2]);
    }

    public function testCheckNecessaryFields()
    {
        $expect = ['用户名'];
        $importer = new ClassroomMemberImporter($this->getBiz());
        $result = ReflectionUtils::invokeMethod(
            $importer,
            'checkNecessaryFields',
            [$expect]
        );
        $this->assertTrue($result);
    }

    public function testTryImport()
    {
        $importer = new ClassroomMemberImporter($this->getBiz());
        $request = new Request([], ['classroomId' => 111]);
        $this->mockBiz(
            'Classroom:ClassroomService',
            [
                [
                    'functionName' => 'tryManageClassroom',
                    'withParams' => [111],
                ],
            ]
        );
        $result = $importer->tryImport($request);
        $this->getClassroomService()->shouldHaveReceived('tryManageClassroom');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
    }
}
