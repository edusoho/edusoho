<?php

namespace Tests\Unit\Live\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Live\Dao\LiveStatisticsDao;
use Biz\Live\LiveStatisticsProcessor\LiveStatisticsProcessorFactory;
use Biz\Live\Service\Impl\LiveStatisticsServiceImpl;
use Biz\Live\Service\LiveStatisticsService;
use Biz\Util\EdusohoLiveClient;

class LiveStatisticsServiceTest extends BaseTestCase
{
    public function testCreateLiveCheckinStatistics()
    {
        $this->mockLiveClient(LiveStatisticsService::STATISTICS_TYPE_CHECKIN);
        $mockedProcessor = $this->mockProcessor();

        ReflectionUtils::setStaticProperty(new LiveStatisticsProcessorFactory(), 'mockedProcessor', $mockedProcessor);

        $liveId = 1;
        $result = $this->getLiveStatisticsService()->createLiveCheckinStatistics($liveId);

        $mockedProcessor->shouldHaveReceived('handlerResult')->times(1);

        $this->assertEquals($liveId, $result['liveId']);
        $this->assertEquals(LiveStatisticsService::STATISTICS_TYPE_CHECKIN, $result['type']);
        $this->assertEquals(['data' => [
            'success' => 1,
            'detail' => 'test detail',
        ]], $result['data']);
    }

    public function testCreateLiveVisitorStatistics()
    {
        $this->mockLiveClient(LiveStatisticsService::STATISTICS_TYPE_VISITOR);
        $mockedProcessor = $this->mockProcessor();

        ReflectionUtils::setStaticProperty(new LiveStatisticsProcessorFactory(), 'mockedProcessor', $mockedProcessor);

        $liveId = 1;
        $result = $this->getLiveStatisticsService()->createLiveVisitorStatistics($liveId);

        $mockedProcessor->shouldHaveReceived('handlerResult')->times(1);

        $this->assertEquals($liveId, $result['liveId']);
        $this->assertEquals(LiveStatisticsService::STATISTICS_TYPE_VISITOR, $result['type']);
        $this->assertEquals(['data' => ['success' => 1,
            'detail' => 'test detail', ]], $result['data']);
    }

    public function testGetCheckinStatisticsByLiveId()
    {
        $liveId = 1;
        $result = $this->getLiveStatisticsService()->getCheckinStatisticsByLiveId($liveId);
        $this->assertNull($result);

        $existedCheckin = $this->createCheckinStatistics($liveId);
        $existedVisitor = $this->createVisitorStatistics($liveId);

        $result = $this->getLiveStatisticsService()->getCheckinStatisticsByLiveId($liveId);

        $this->assertEquals($existedCheckin['id'], $result['id']);
    }

    public function testGetVisitorStatisticsByLiveId()
    {
        $liveId = 1;
        $result = $this->getLiveStatisticsService()->getVisitorStatisticsByLiveId($liveId);
        $this->assertNull($result);

        $existedCheckin = $this->createCheckinStatistics($liveId);
        $existedVisitor = $this->createVisitorStatistics($liveId);

        $result = $this->getLiveStatisticsService()->getVisitorStatisticsByLiveId($liveId);

        $this->assertEquals($existedVisitor['id'], $result['id']);
    }

    public function testUpdateCheckinStatistics_WithoutExistedStatistics()
    {
        $this->mockLiveClient(LiveStatisticsService::STATISTICS_TYPE_CHECKIN);
        $mockedProcessor = $this->mockProcessor();

        ReflectionUtils::setStaticProperty(new LiveStatisticsProcessorFactory(), 'mockedProcessor', $mockedProcessor);

        $liveId = 1;
        $result = $this->getLiveStatisticsService()->updateCheckinStatistics($liveId);

        $mockedProcessor->shouldHaveReceived('handlerResult')->times(1);

        $this->assertEquals($liveId, $result['liveId']);
        $this->assertEquals(LiveStatisticsService::STATISTICS_TYPE_CHECKIN, $result['type']);
        $this->assertEquals(['data' => ['success' => 1,
            'detail' => 'test detail', ]], $result['data']);
    }

    public function testUpdateCheckinStatistics_WithExistedStatistics()
    {
        $this->mockLiveClient(LiveStatisticsService::STATISTICS_TYPE_CHECKIN);
        $mockedProcessor = $this->mockProcessor();

        ReflectionUtils::setStaticProperty(new LiveStatisticsProcessorFactory(), 'mockedProcessor', $mockedProcessor);

        $liveId = 1;

        $existed = $this->createCheckinStatistics($liveId);
        $result = $this->getLiveStatisticsService()->updateCheckinStatistics($liveId);

        $mockedProcessor->shouldHaveReceived('handlerResult')->times(1);

        $this->assertEquals($liveId, $existed['liveId']);
        $this->assertEquals(LiveStatisticsService::STATISTICS_TYPE_CHECKIN, $existed['type']);
        $this->assertEmpty($existed['data']);

        $this->assertEquals($liveId, $result['liveId']);
        $this->assertEquals(LiveStatisticsService::STATISTICS_TYPE_CHECKIN, $result['type']);
        $this->assertEmpty($result['data']);
    }

    public function testUpdateVisitorStatistics_WithoutExistedStatistics()
    {
        $this->mockLiveClient(LiveStatisticsService::STATISTICS_TYPE_VISITOR);
        $mockedProcessor = $this->mockProcessor();

        ReflectionUtils::setStaticProperty(new LiveStatisticsProcessorFactory(), 'mockedProcessor', $mockedProcessor);

        $liveId = 1;
        $result = $this->getLiveStatisticsService()->updateVisitorStatistics($liveId);

        $mockedProcessor->shouldHaveReceived('handlerResult')->times(1);

        $this->assertEquals($liveId, $result['liveId']);
        $this->assertEquals(LiveStatisticsService::STATISTICS_TYPE_VISITOR, $result['type']);
        $this->assertEquals(['data' => ['success' => 1,
            'detail' => 'test detail', ]], $result['data']);
    }

    public function testUpdateVisitorStatistics_WithExistedStatistics()
    {
        $this->mockLiveClient(LiveStatisticsService::STATISTICS_TYPE_VISITOR);
        $mockedProcessor = $this->mockProcessor();

        ReflectionUtils::setStaticProperty(new LiveStatisticsProcessorFactory(), 'mockedProcessor', $mockedProcessor);

        $liveId = 1;

        $existed = $this->createVisitorStatistics($liveId);
        $result = $this->getLiveStatisticsService()->updateVisitorStatistics($liveId);

        $mockedProcessor->shouldHaveReceived('handlerResult')->times(1);

        $this->assertEquals($liveId, $existed['liveId']);
        $this->assertEquals(LiveStatisticsService::STATISTICS_TYPE_VISITOR, $existed['type']);
        $this->assertEmpty($existed['data']);

        $this->assertEquals($liveId, $result['liveId']);
        $this->assertEquals(LiveStatisticsService::STATISTICS_TYPE_VISITOR, $result['type']);
        $this->assertEmpty($result['data']);
    }

    public function testFindCheckinStatisticsByLiveIds()
    {
        $liveIds = [1, 2, 4];
        $result = $this->getLiveStatisticsService()->findCheckinStatisticsByLiveIds($liveIds);

        $this->assertEmpty($result);

        $expected = [];
        $expected[1] = $this->createCheckinStatistics(1);
        $expected[2] = $this->createCheckinStatistics(2);
        $this->createCheckinStatistics(3);

        $result = $this->getLiveStatisticsService()->findCheckinStatisticsByLiveIds($liveIds);

        $this->assertCount(2, $result);
        $this->assertEquals($expected, $result);
    }

    public function testFindVisitorStatisticsByLiveIds()
    {
        $liveIds = [1, 2, 4];
        $result = $this->getLiveStatisticsService()->findVisitorStatisticsByLiveIds($liveIds);

        $this->assertEmpty($result);

        $expected = [];
        $expected[1] = $this->createVisitorStatistics(1);
        $expected[2] = $this->createVisitorStatistics(2);
        $this->createVisitorStatistics(3);

        $result = $this->getLiveStatisticsService()->findVisitorStatisticsByLiveIds($liveIds);

        $this->assertCount(2, $result);
        $this->assertEquals($expected, $result);
    }

    public function testGenerateStatisticsByLiveIdAndTypeWithCheckIn()
    {
        $mockedProcessor = $this->mockProcessor();
        ReflectionUtils::setStaticProperty(new LiveStatisticsProcessorFactory(), 'mockedProcessor', $mockedProcessor);

        $this->biz['qiQiuYunSdk.s2b2cService'] = $this->mockBiz(
            'qiQiuYunSdk.s2b2cService',
            [
                [
                    'functionName' => 'getLiveRoomCheckinList',
                    'returnValue' => [['code' => 200, 'data' => [
                        ['time' => 1592394136, 'liveId' => 1, 'users' => [
                            ['nickName' => 'admin_1'],
                        ]],
                    ]]],
                    'withParams' => [1],
                ],
            ]
        );

        $this->mockBiz(
            'Activity:LiveActivityService',
            [
                [
                    'functionName' => 'getBySyncIdGTAndLiveId',
                    'returnValue' => ['id' => 1],
                ],
            ]
        );

        $liveId = 1;
        $result = ReflectionUtils::invokeMethod(new LiveStatisticsServiceImpl($this->biz), 'generateStatisticsByLiveIdAndType', [$liveId, LiveStatisticsService::STATISTICS_TYPE_CHECKIN]);

        $this->assertEquals($liveId, $result['liveId']);
        $this->assertEquals(LiveStatisticsService::STATISTICS_TYPE_CHECKIN, $result['type']);
    }

    public function testGenerateStatisticsByLiveIdAndTypeWithVisitor()
    {
        $mockedProcessor = $this->mockProcessor();
        ReflectionUtils::setStaticProperty(new LiveStatisticsProcessorFactory(), 'mockedProcessor', $mockedProcessor);

        $this->biz['qiQiuYunSdk.s2b2cService'] = $this->mockBiz(
            'qiQiuYunSdk.s2b2cService',
            [
                [
                    'functionName' => 'getLiveRoomHistory',
                    'returnValue' => [['code' => 200, 'data' => [
                        ['time' => 1592394136, 'liveId' => 1, 'users' => [
                            ['nickName' => 'admin_1', 'joinTime' => 1592394136, 'leaveTime' => 1592494136],
                        ]],
                    ]]],
                    'withParams' => [1],
                ],
            ]
        );

        $this->mockBiz(
            'Activity:LiveActivityService',
            [
                [
                    'functionName' => 'getBySyncIdGTAndLiveId',
                    'returnValue' => ['id' => 1],
                ],
            ]
        );

        $liveId = 1;
        $result = ReflectionUtils::invokeMethod(new LiveStatisticsServiceImpl($this->biz), 'generateStatisticsByLiveIdAndType', [$liveId, LiveStatisticsService::STATISTICS_TYPE_VISITOR]);

        $this->assertEquals($liveId, $result['liveId']);
        $this->assertEquals(LiveStatisticsService::STATISTICS_TYPE_VISITOR, $result['type']);
    }

    protected function createCheckinStatistics($liveId)
    {
        return $this->getLiveStatisticsDao()->create(['liveId' => $liveId, 'type' => LiveStatisticsService::STATISTICS_TYPE_CHECKIN, 'data' => []]);
    }

    protected function createVisitorStatistics($liveId)
    {
        return $this->getLiveStatisticsDao()->create(['liveId' => $liveId, 'type' => LiveStatisticsService::STATISTICS_TYPE_VISITOR, 'data' => []]);
    }

    protected function mockProcessor()
    {
        return $this->mockBiz(
            'Mocked:MockedProcessor',
            [
                [
                    'functionName' => 'handlerResult',
                    'returnValue' => [
                        'data' => [
                            'success' => 1,
                            'detail' => 'test detail',
                        ],
                    ],
                ],
            ]
        );
    }

    protected function mockLiveClient($type)
    {
        $liveClient = new EdusohoLiveClient();
        $mockObject = \Mockery::mock($liveClient);

        if (LiveStatisticsService::STATISTICS_TYPE_CHECKIN == $type) {
            $mockObject->shouldReceive('getLiveRoomCheckinList')->times(1)->andReturn([
                'code' => 0,
                'data' => [],
            ]);
        }

        if (LiveStatisticsService::STATISTICS_TYPE_VISITOR == $type) {
            $mockObject->shouldReceive('getLiveRoomHistory')->times(1)->andReturn([
                'code' => 0,
                'data' => [],
            ]);
        }

        $biz = $this->getBiz();
        $biz['educloud.live_client'] = $mockObject;
    }

    /**
     * @return LiveStatisticsService
     */
    protected function getLiveStatisticsService()
    {
        return $this->createService('Live:LiveStatisticsService');
    }

    /**
     * @return LiveStatisticsDao
     */
    protected function getLiveStatisticsDao()
    {
        return $this->createDao('Live:LiveStatisticsDao');
    }
}
