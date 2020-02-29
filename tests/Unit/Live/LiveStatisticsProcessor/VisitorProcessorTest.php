<?php

namespace Tests\Unit\Live\LiveStatisticsProcessor;

use Biz\BaseTestCase;
use Biz\Live\LiveStatisticsProcessor\VisitorProcessor;

class VisitorProcessorTest extends BaseTestCase
{
    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     * @expectedExceptionMessage code is not found
     */
    public function testHandlerResult_Exception_CodeNotFound()
    {
        $processor = new VisitorProcessor($this->getBiz());

        $processor->handlerResult(array('data' => array()));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     * @expectedExceptionMessage code is not valid
     */
    public function testHandlerResult_Exception_CodeNotValid()
    {
        $processor = new VisitorProcessor($this->getBiz());

        $processor->handlerResult(array('code' => 5001, 'data' => array()));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     * @expectedExceptionMessage data is not found
     */
    public function testHandlerResult_Exception_DataNotFound()
    {
        $processor = new VisitorProcessor($this->getBiz());

        $processor->handlerResult(array('code' => VisitorProcessor::RESPONSE_CODE_NOT_FOUND));
    }

    public function testHandleResult_Empty()
    {
        $result = array(
            'code' => VisitorProcessor::RESPONSE_CODE_SUCCESS,
            'data' => array(),
        );
        $processor = new VisitorProcessor($this->getBiz());
        $result = $processor->handlerResult($result);
        $this->assertEmpty($result);
    }

    public function testHandleResult_NotSuccess()
    {
        $preResult = $this->mockResult();

        $processor = new VisitorProcessor($this->getBiz());
        $result = $processor->handlerResult($preResult);

        $this->assertEquals(0, $result['totalLearnTime']);
        $this->assertEquals(0, $result['success']);
        $this->assertEmpty($result['detail']);
    }

    public function testHandleResult_UserNotExist()
    {
        $preResult = array(
            'code' => '0',
            'liveId' => 1,
            'data' => array(
                array(
                    'joinTime' => 1581576156000,
                    'leaveTime' => 1581576256000,
                    'nickName' => 'test_6',
                ),
            ),
        );

        $this->mockCourseTeachers();

        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'getUser',
                'returnValue' => array(array()),
            ),
        ));

        $expected = array(
            'totalLearnTime' => 0,
            'success' => 1,
            'detail' => array(),
        );
        $processor = new VisitorProcessor($this->getBiz());
        $result = $processor->handlerResult($preResult);

        $this->assertEquals($expected, $result);
    }

    public function testHandleResult_UserExist()
    {
        $preResult = $this->mockResult(true);

        $this->mockCourseTeachers();

        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'getUser',
                'returnValue' => array(array('nickname' => 'name')),
            ),
        ));

        $processor = new VisitorProcessor($this->getBiz());
        $result = $processor->handlerResult($preResult);

        $this->assertCount(7, $preResult['data']);
        $this->assertCount(4, $result['detail']);
//        $this->assertEquals($expected, $result);
    }

    protected function mockCourseTeachers()
    {
        $this->mockBiz('Activity:LiveActivityService', array(
            array(
                'functionName' => 'getByLiveId',
                'returnValue' => array(
                        'id' => 1,
                ),
            ),
        ));

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getByMediaIdAndMediaTypeAndCopyId',
                'returnValue' => array(
                        'fromCourseId' => 1,
                ),
            ),
        ));

        $this->mockBiz('Course:MemberService', array(
            array(
                'functionName' => 'findCourseTeachers',
                'returnValue' => array(
                    array('userId' => 6),
                    array('userId' => 5),
                ),
            ),
        ));
    }

    protected function mockResult($isNicknameValid = false)
    {
        return array(
            'code' => '0',
            'liveId' => 1,
            'data' => array(
                array(
                    'joinTime' => 1581576156000,
                    'leaveTime' => 1581576256000,
                    'nickName' => $isNicknameValid ? 'test_1' : 'test1',
                ),
                array(
                    'joinTime' => 1581576356000,
                    'leaveTime' => 1581576456000,
                    'nickName' => $isNicknameValid ? 'test_2' : 'test2',
                ),
                array(
                    'joinTime' => 1581576556000,
                    'leaveTime' => 1581576656000,
                    'nickName' => $isNicknameValid ? 'test_3' : 'test3',
                ),
                array(
                    'joinTime' => 1581576756000,
                    'leaveTime' => 1581576856000,
                    'nickName' => $isNicknameValid ? 'test_4' : 'test4',
                ),
                array(
                    'joinTime' => 1581576956000,
                    'leaveTime' => 1581576966000,
                    'nickName' => $isNicknameValid ? 'test_2' : 'test2',
                ),
                array(
                    'joinTime' => 1581576416000,
                    'leaveTime' => 1581576426000,
                    'nickName' => $isNicknameValid ? 'test_3' : 'test3',
                ),
                array(
                    'joinTime' => 1581576436000,
                    'leaveTime' => 1581576446000,
                    'nickName' => $isNicknameValid ? 'test_6' : 'test6',
                ),
            ),
        );
    }
}
