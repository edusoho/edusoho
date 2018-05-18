<?php

namespace Biz\Testpaper\Tests;

use Biz\BaseTestCase;
use AppBundle\Common\TimeMachine;
use Biz\Testpaper\Builder\ExerciseBuilder;

class ExerciseBuilderTest extends BaseTestCase
{
    public function testUpdateSubmitedResult()
    {
        $mockedTestpaperService = $this->mockBiz(
            'Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'getTestpaperResult',
                    'withParams' => array(111),
                    'returnValue' => array(
                        'id' => 222,
                    ),
                ),
                array(
                    'functionName' => 'findItemResultsByResultId',
                    'withParams' => array(222),
                    'returnValue' => array(
                        'itemResults' => 'result',
                    ),
                ),
                array(
                    'functionName' => 'sumScore',
                    'withParams' => array(array('itemResults' => 'result')),
                    'returnValue' => array(
                        'sumScore' => 9,
                        'rightItemCount' => 7,
                    ),
                ),
                array(
                    'functionName' => 'updateTestpaperResult',
                    'withParams' => array(222, array(
                        'status' => 'finished',
                        'metas' => array(
                            'orders' => array(),
                        ),
                        'score' => 9,
                        'rightItemCount' => 7,
                        'usedTime' => 1525162415,
                        'endTime' => 1525162415,
                        'checkedTime' => 1525162415,
                    )),
                    'returnValue' => 123,
                ),
            )
        );

        TimeMachine::setMockedTime(1525162415);
        $builder = new ExerciseBuilder($this->getBiz());
        $result = $builder->updateSubmitedResult(111, 1525162415, null);

        $mockedTestpaperService->shouldHaveReceived('getTestpaperResult')->times(1);
        $mockedTestpaperService->shouldHaveReceived('findItemResultsByResultId')->times(1);
        $mockedTestpaperService->shouldHaveReceived('sumScore')->times(1);
        $mockedTestpaperService->shouldHaveReceived('updateTestpaperResult')->times(1);

        $this->assertEquals(123, $result);
    }

    protected function getTestpaperService()
    {
        return $this->createDao('Testpaper:TestpaperService');
    }
}
