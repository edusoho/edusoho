<?php

namespace Tests\Unit\Importer;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Importer\CourseMemberImporter;
use Symfony\Component\HttpFoundation\Request;

class CourseMemberImporterTest extends BaseTestCase
{
    public function testImport()
    {
        $request = new Request(
            array(),
            array(
                'importData' => array(),
                'courseId' => 1,
                'price' => 1,
            )
        );

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'withParams' => array(1), 'returnValue' => array()),
        ));

        $importer = new CourseMemberImporter($this->biz);
        $result = $importer->import($request);

        $this->assertEquals(array('existsUserCount' => 0, 'successCount' => 0), $result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testTryImport()
    {
        $request = new Request(
            array(),
            array(
                'courseId' => 999,
            )
        );

        $importer = new CourseMemberImporter($this->biz);
        $importer->tryImport($request);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetTemplate()
    {
        $request = new Request(
            array(
                'courseId' => 1,
            )
        );
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'withParams' => array(1), 'returnValue' => array(
                'id' => 1,
                'price' => 100,
            )),
        ));

        $importer = new CourseMemberImporter($this->biz);
        $importer->getTemplate($request);
    }

    public function testExcelDataImporting()
    {
        $userData = array(
            array('nickname' => 'admin'),
            array('email' => 'es@qq.com'),
            array('verifiedMobile' => '15067160366'),
        );

        $this->mockBiz('User:UserService', array(
            array('functionName' => 'getUserByNickname', 'returnValue' => array('id' => 1)),
            array('functionName' => 'getUserByEmail', 'returnValue' => array('id' => 2)),
            array('functionName' => 'getUserByVerifiedMobile', 'returnValue' => array('id' => 3)),
        ));

        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'isCourseStudent', 'andReturnValues' => array(
                false, false, true,
            )),
            array('functionName' => 'isCourseTeacher', 'andReturnValues' => array(
                true, false, false,
            )),
            array('functionName' => 'becomeStudentAndCreateOrder', 'returnValue' => array()),
        ));

        $importer = new CourseMemberImporter($this->biz);
        $result = ReflectionUtils::invokeMethod($importer, 'excelDataImporting', array(array('id' => 1), $userData, array('amount' => 0)));
        $this->assertEquals(2, $result['existsUserCount']);
        $this->assertEquals(1, $result['successCount']);
    }
}
