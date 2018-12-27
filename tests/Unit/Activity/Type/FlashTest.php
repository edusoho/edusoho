<?php

namespace Tests\Unit\Activity\Type;

use AppBundle\Common\ReflectionUtils;

class FlashTest extends BaseTypeTestCase
{
    const TYPE = 'flash';

    public function testCreate()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $flashActivity = $type->create($field);

        $this->assertEquals('end', $flashActivity['finishType']);
        $this->assertEquals(0, $flashActivity['finishDetail']);
        $this->assertEquals(1, $flashActivity['mediaId']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testCreateWithMediaEmpty()
    {
        $field = $this->mockField();
        unset($field['media']);

        $type = $this->getActivityConfig(self::TYPE);
        $type->create($field);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testCreateWithMediaIdEmpty()
    {
        $field = $this->mockField($finishType = 'end', $finishDetail = 0, $source = 'self', $uri = '', $mediaId = 0);

        $type = $this->getActivityConfig(self::TYPE);
        $type->create($field);
    }

    public function testUpdate()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $flashActivity = $type->create($field);
        $activity = $this->mockSimpleActivity($flashActivity['id']);

        $update = $this->mockField('end', 1, 'self', '', 2);
        $updated = $type->update($flashActivity['id'], $update, $activity);

        $this->assertEquals(2, $updated['mediaId']);
        $this->assertEquals('end', $updated['finishType']);
        $this->assertEquals(1, $updated['finishDetail']);
    }

    public function testCopy()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $flashActivity = $type->create($field);

        $copy = $type->copy($flashActivity);

        $this->assertEquals($flashActivity['mediaId'], $copy['mediaId']);
        $this->assertEquals($flashActivity['finishType'], $copy['finishType']);
        $this->assertEquals($flashActivity['finishDetail'], $copy['finishDetail']);
    }

    public function testSync()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $field1 = $this->mockField();
        $flashActivity1 = $type->create($field1);
        $activity1 = $this->mockSimpleActivity($flashActivity1['id']);
        $field2 = $this->mockField('time', 1, 'self', '', 3);
        $flashActivity2 = $type->create($field2);
        $activity2 = $this->mockSimpleActivity($flashActivity2['id']);

        $syncedActivity = $type->sync($activity1, $activity2);

        $this->assertNotEquals($flashActivity1['finishType'], $flashActivity2['finishType']);
        $this->assertNotEquals($flashActivity1['finishDetail'], $flashActivity2['finishDetail']);
        $this->assertNotEquals($flashActivity1['mediaId'], $flashActivity2['mediaId']);

        $this->assertEquals($flashActivity1['finishType'], $syncedActivity['finishType']);
        $this->assertEquals($flashActivity1['finishDetail'], $syncedActivity['finishDetail']);
        $this->assertEquals($flashActivity1['mediaId'], $syncedActivity['mediaId']);
    }

    public function testGet()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $flashActivity = $type->create($field);

        $result = $type->get($flashActivity['id']);

        $this->assertEquals($flashActivity['mediaId'], $result['mediaId']);
        $this->assertEquals($flashActivity['finishType'], $result['finishType']);
        $this->assertEquals($flashActivity['finishDetail'], $result['finishDetail']);
    }

    public function testFind()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $audioActivity = $type->create($field);

        $results = $type->find(array($audioActivity['id']));

        $this->assertEquals(1, count($results));
    }

    public function testDelete()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $flashActivity = $type->create($field);
        $pre = $type->get($flashActivity['id']);
        $this->assertFalse(empty($pre['id']));

        $type->delete($flashActivity['id']);
        $result = $type->get($flashActivity['id']);
        $this->assertEmpty($result['file']);
        $this->assertTrue(empty($result['id']));
    }

    public function testRegisterListeners()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $return = ReflectionUtils::invokeMethod($type, 'registerListeners');

        $this->assertEquals(null, $return);
    }

    public function testMaterialSupported()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->assertEquals(true, $type->materialSupported());
    }

    public function testIsFinished()
    {
        $field = $this->mockField();
        $type = $this->getActivityConfig(self::TYPE);
        $flashActivity = $type->create($field);
        $activity = $this->mockSimpleActivity($flashActivity['id']);

        $this->mockBiz('Activity:ActivityService', array(
            array('functionName' => 'getActivity', 'returnValue' => $activity),
        ));

        $result = $type->isFinished(1);
        $this->assertFalse($result);
        $this->mockBiz('Task:TaskResultService', array(
            array(
                'functionName' => 'getMyLearnedTimeByActivityId',
                'returnValue' => 100,
                'withParams' => array(1),
            ),
        ));
        $result = $type->isFinished(1);
        $this->assertTrue($result);
    }

    /**
     * @param string $finishType
     * @param int    $finishDetail
     * @param string $source
     * @param string $uri
     * @param int    $mediaId
     *
     * @return array
     */
    private function mockField($finishType = 'end', $finishDetail = 0, $source = 'self', $uri = '', $mediaId = 1)
    {
        return array(
            'finishType' => $finishType,
            'finishDetail' => $finishDetail,
            'mediaId' => $mediaId,
            'media' => json_encode(array(
                'source' => $source,
                'uri' => $uri,
                'id' => $mediaId,
            )),
        );
    }

    /**
     * @param int $mediaId
     *
     * @return array
     */
    private function mockSimpleActivity($mediaId = 1)
    {
        return array(
            'id' => 1,
            'mediaId' => $mediaId,
            'finishType' => 'time',
            'finishData' => 1,
        );
    }
}
