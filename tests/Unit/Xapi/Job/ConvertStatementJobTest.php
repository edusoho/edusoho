<?php

namespace Tests\Unit\Xapi\Job;

use Biz\Xapi\Job\ConvertStatementJob;
use Biz\BaseTestCase;
use AppBundle\Common\TimeMachine;

class ConvertStatementJobTest extends BaseTestCase
{
    public function testExecute()
    {
        TimeMachine::setMockedTime(1524388123);
        $mockedStatementDao = $this->mockBiz(
            'Xapi:StatementDao',
            array(
                array(
                    'functionName' => 'retryStatusPushingToCreatedByCreatedTime',
                    'withParams' => array(1524388123 - 86400 * 3),
                ),
            )
        );

        $mockedXapiService = $this->mockBiz(
            'Xapi:XapiService',
            array(
                array(
                    'functionName' => 'searchStatements',
                    'withParams' => array(
                        array('status' => 'created'),
                        array('created_time' => 'DESC'),
                        0,
                        2000,
                    ),
                    'returnValue' => array(
                        array(
                            'verb' => 'asked',
                            'target_type' => 'question',
                            'uuid' => 1231,
                            'id' => 33,
                        ),
                    ),
                ),
                array(
                    'functionName' => 'updateStatementsConvertedAndDataByStatementData',
                    'withParams' => array(array(33 => array('id' => 1231))),
                ),
            )
        );

        $mockedPush = $this->mockPureBiz(
            'xapi.push.asked_question',
            array(
                array(
                    'functionName' => 'packages',
                    'withParams' => array(
                        array(
                            array(
                                'verb' => 'asked',
                                'target_type' => 'question',
                                'uuid' => 1231,
                                'key' => 'asked_question',
                                'id' => 33,
                            ),
                        ),
                    ),
                    'returnValue' => array(
                        array(
                            'id' => 1231,
                        ),
                    ),
                ),
            )
        );
        $job = new ConvertStatementJob(array(), $this->getBiz());
        $job->execute();

        $mockedStatementDao->shouldHaveReceived('retryStatusPushingToCreatedByCreatedTime');
        $mockedXapiService->shouldHaveReceived('searchStatements');
        $mockedPush->shouldHaveReceived('packages');
        $mockedXapiService->shouldHaveReceived('updateStatementsConvertedAndDataByStatementData');

        $this->assertTrue(true);
    }
}
