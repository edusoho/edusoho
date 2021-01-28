<?php

namespace Tests\Unit\Live\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class LiveStatisticsDaoTest extends BaseDaoTestCase
{
    public function testGetByLiveIdAndType()
    {
        $checkin = array(
            'liveId' => 2,
            'type' => 'checkin',
            'data' => array(
                'time' => 1582705087,
                'success' => 1,
                'detail' => array(
                    array(
                        'nickName' => 'wjc1234_3',
                        'checkin' => 1,
                        'nickname' => 'sansan',
                        'userId' => '3',
                    ),
                ),
            ),
        );
        $checkin = $this->mockDataObject($checkin);
        $visitor = $this->mockDataObject();

        $result = $this->getDao()->getByLiveIdAndType($checkin['liveId'], $checkin['type']);
        $this->assertEquals($checkin['data'], $result['data']);

        $result = $this->getDao()->getByLiveIdAndType($visitor['liveId'], $visitor['type']);

        return $this->assertEquals($visitor['data'], $result['data']);
    }

    public function testFindByLiveIdsAndType()
    {
        $checkin = array(
            'type' => 'checkin',
            'data' => array(
            ),
        );
        $checkin = $this->mockDataObject($checkin);
        $visitor1 = $this->mockDataObject();
        $visitor2 = $this->mockDataObject(array('liveId' => 2));
        $visitor3 = $this->mockDataObject(array('liveId' => 3));

        $result = $this->getDao()->findByLiveIdsAndType(array(1, 2), 'visitor');
        $this->assertCount(2, $result);
        $this->assertEquals($visitor1['id'], $result[0]['id']);
        $this->assertEquals($visitor2['id'], $result[1]['id']);

        $result = $this->getDao()->findByLiveIdsAndType(array(1, 2), 'checkin');
        $this->assertCount(1, $result);
        $this->assertEquals($checkin['id'], $result[0]['id']);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'type' => 'visitor',
            'liveId' => 1,
            'data' => array(
                'totalLearnTime' => 198,
                'success' => 1,
                'detail' => array(
                    '3' => array(
                        'userId' => '3',
                        'nickname' => 'testname',
                        'firstJoin' => 1582705068,
                        'lastLeave' => 1582705266,
                        'learnTime' => 198,
                    ),
                ),
            ),
            'liveId' => 1,
        );
    }
}
