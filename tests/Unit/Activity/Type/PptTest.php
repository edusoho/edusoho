<?php

namespace Tests\Unit\Activity\Type;

use AppBundle\Common\ReflectionUtils;

class PptTest extends BaseTypeTestCase
{
    const TYPE = 'ppt';

    public function testCreate()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $pptActivity = $type->create($field);

        $this->assertEquals(1, $pptActivity['mediaId']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateWithMediaEmpty()
    {
        $field = $this->mockField();
        unset($field['media']);

        $type = $this->getActivityConfig(self::TYPE);
        $type->create($field);
    }

    public function testUpdate()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $pptActivity = $type->create($field);
        $activity = $this->mockSimpleActivity($pptActivity['id']);

        $update = $this->mockField('end', 1,  2);
        $updated = $type->update($pptActivity['id'], $update, $activity);

        $this->assertEquals(2, $updated['mediaId']);
        $this->assertEquals('end', $updated['finishType']);
        $this->assertEquals(1, $updated['finishDetail']);
    }

    public function testCopy()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $pptActivity = $type->create($field);

        $copy = $type->copy($pptActivity);

        $this->assertEquals($pptActivity['mediaId'], $copy['mediaId']);
    }

    public function testSync()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $field1 = $this->mockField();
        $pptActivity1 = $type->create($field1);
        $activity1 = $this->mockSimpleActivity($pptActivity1['id']);
        $field2 = $this->mockField(3, 1, 2);
        $pptActivity2 = $type->create($field2);
        $activity2 = $this->mockSimpleActivity($pptActivity2['id']);

        $syncedActivity = $type->sync($activity1, $activity2);
        $this->assertNotEquals($pptActivity1['mediaId'], $pptActivity2['mediaId']);

        $this->assertEquals($pptActivity1['mediaId'], $syncedActivity['mediaId']);
    }

    public function testGet()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $pptActivity = $type->create($field);

        $result = $type->get($pptActivity['id']);

        $this->assertEquals($pptActivity['mediaId'], $result['mediaId']);
    }

    public function testMaterialSupported()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->assertEquals(true, $type->materialSupported());
    }

    public function testFind()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $pptActivity = $type->create($field);

        $results = $type->find(array($pptActivity['id']));

        $this->assertEquals(1, count($results));
    }

    public function testDelete()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $pptActivity = $type->create($field);
        $pre = $type->get($pptActivity['id']);
        $this->assertFalse(empty($pre['id']));

        $type->delete($pptActivity['id']);
        $result = $type->get($pptActivity['id']);

        $this->assertEmpty($result['file']);
        $this->assertTrue(empty($result['id']));
    }

    public function testIsFinished()
    {
        $field = $this->mockField();
        $type = $this->getActivityConfig(self::TYPE);
        $pptActivity = $type->create($field);
        $activity = $this->mockSimpleActivity($pptActivity['id']);

        $this->mockBiz('Activity:ActivityService', array(
            array('functionName' => 'getActivity', 'returnValue' => $activity),
        ));

        $result = $type->isFinished(1);
        $this->assertFalse($result);
    }

    public function testRegisterListeners()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $return = ReflectionUtils::invokeMethod($type, 'registerListeners');

        $this->assertEquals(null, $return);
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
    private function mockField($finishType = 'end', $finishDetail = 0, $mediaId = 1)
    {
        return array(
            'finishType' => $finishType,
            'finishDetail' => $finishDetail,
            'mediaId' => $mediaId,
            'media' => json_encode(array(
                'id' => $mediaId,
            ))
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
        );
    }
}
