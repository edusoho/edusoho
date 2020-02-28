<?php

namespace Tests\Unit\Live\LiveStatisticsProcessor;

use Biz\BaseTestCase;
use Biz\Live\LiveStatisticsProcessor\CheckinProcessor;

class CheckinProcessorTest extends BaseTestCase
{
    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     * @expectedExceptionMessage code is not found
     */
    public function testHandlerResult_Exception_CodeNotFound()
    {
        $processor = new CheckinProcessor($this->getBiz());

        $processor->handlerResult(array('data' => array()));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     * @expectedExceptionMessage code is not valid
     */
    public function testHandlerResult_Exception_CodeNotValid()
    {
        $processor = new CheckinProcessor($this->getBiz());

        $processor->handlerResult(array('code' => 5001, 'data' => array()));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     * @expectedExceptionMessage data is not found
     */
    public function testHandlerResult_Exception_DataNotFound()
    {
        $processor = new CheckinProcessor($this->getBiz());

        $processor->handlerResult(array('code' => CheckinProcessor::RESPONSE_CODE_NOT_FOUND));
    }

    public function testHandleResult_Empty()
    {
        $result = array(
            'code' => CheckinProcessor::RESPONSE_CODE_SUCCESS,
            'data' => array(),
        );
        $processor = new CheckinProcessor($this->getBiz());
        $result = $processor->handlerResult($result);
        $this->assertEmpty($result);
    }

    public function testHandleResult_NotSuccess()
    {
        $preResult = array(
            'code' => CheckinProcessor::RESPONSE_CODE_SUCCESS,
            'data' => array(
                array(
                    'time' => 1581576396000,
                    'users' => array(
                        array(
                            'nickName' => 'test',
                            'checkin' => 1,
                        ),
                    ),
                ),
            ),
        );
        $processor = new CheckinProcessor($this->getBiz());
        $result = $processor->handlerResult($preResult);

        $this->assertEquals($preResult['data'][0]['time'] / 1000, $result['time']);
        $this->assertEquals(0, $result['success']);
        $this->assertEmpty($result['detail']);
    }

    public function testHandleResult_UserNotExist()
    {
        $preResult = array(
            'code' => CheckinProcessor::RESPONSE_CODE_SUCCESS,
            'data' => array(
                array(
                    'time' => 1581576396000,
                    'users' => array(
                        array(
                            'nickName' => 'test_2',
                            'checkin' => 1,
                        ),
                    ),
                ),
            ),
        );

        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'getUser',
                'returnValue' => array(),
            ),
        ));
        $expected = array(
            'time' => $preResult['data'][0]['time'] / 1000,
            'success' => 1,
            'detail' => array(
                array(
                    'nickName' => 'test_2',
                    'checkin' => 1,
                    'nickname' => 'test_2',
                    'userId' => 2,
                ),
            ),
        );
        $processor = new CheckinProcessor($this->getBiz());
        $result = $processor->handlerResult($preResult);

        $this->assertEquals($expected, $result);
    }

    public function testHandleResult_UserExist()
    {
        $preResult = array(
            'code' => CheckinProcessor::RESPONSE_CODE_SUCCESS,
            'data' => array(
                array(
                    'time' => 1581576396000,
                    'users' => array(
                        array(
                            'nickName' => 'test_2',
                            'checkin' => 1,
                        ),
                    ),
                ),
            ),
        );

        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'getUser',
                'returnValue' => array(
                    'nickname' => 'name',
                ),
            ),
        ));
        $expected = array(
            'time' => $preResult['data'][0]['time'] / 1000,
            'success' => 1,
            'detail' => array(
                array(
                    'nickName' => 'test_2',
                    'checkin' => 1,
                    'nickname' => 'name',
                    'userId' => 2,
                ),
            ),
        );
        $processor = new CheckinProcessor($this->getBiz());
        $result = $processor->handlerResult($preResult);

        $this->assertEquals($expected, $result);
    }
}
