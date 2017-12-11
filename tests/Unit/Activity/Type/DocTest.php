<?php

namespace Tests\Unit\Activity\Type;

class DocTest extends BaseTypeTestCase
{
    const TYPE = 'doc';

    public function testRegisterActions()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->assertEquals(array(
            'create' => 'AppBundle:Doc:create',
            'edit' => 'AppBundle:Doc:edit',
            'show' => 'AppBundle:Doc:show',
        ), $type->registerActions());
    }

    public function testCreate()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $docActivity = $type->create($field);

        $this->assertEquals(1, $docActivity['mediaId']);
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
        $docActivity = $type->create($field);
        $activity = $this->mockSimpleActivity($docActivity['id']);

        $update = $this->mockField('end', 1,  2);
        $updated = $type->update($docActivity['id'], $update, $activity);

        $this->assertEquals(2, $updated['mediaId']);
        $this->assertEquals('end', $updated['finishType']);
        $this->assertEquals(1, $updated['finishDetail']);
    }

    public function testCopy()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $docActivity = $type->create($field);

        $copy = $type->copy($docActivity);

        $this->assertEquals($docActivity['mediaId'], $copy['mediaId']);
    }

    public function testSync()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $field1 = $this->mockField();
        $docActivity1 = $type->create($field1);
        $activity1 = $this->mockSimpleActivity($docActivity1['id']);
        $field2 = $this->mockField(3, 1, 2);
        $docActivity2 = $type->create($field2);
        $activity2 = $this->mockSimpleActivity($docActivity2['id']);

        $syncedActivity = $type->sync($activity1, $activity2);
        $this->assertNotEquals($docActivity1['mediaId'], $docActivity2['mediaId']);

        $this->assertEquals($docActivity1['mediaId'], $syncedActivity['mediaId']);
    }

    public function testGet()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $docActivity = $type->create($field);

        $result = $type->get($docActivity['id']);

        $this->assertEquals($docActivity['mediaId'], $result['mediaId']);
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
        $docActivity = $type->create($field);

        $results = $type->find(array($docActivity['id']));

        $this->assertEquals(1, count($results));
    }

    public function testDelete()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $docActivity = $type->create($field);
        $pre = $type->get($docActivity['id']);
        $this->assertFalse(empty($pre['id']));

        $type->delete($docActivity['id']);
        $result = $type->get($docActivity['id']);

        $this->assertEmpty($result['file']);
        $this->assertTrue(empty($result['id']));
    }

    public function testIsFinished()
    {
        $field = $this->mockField();
        $type = $this->getActivityConfig(self::TYPE);
        $docActivity = $type->create($field);
        $activity = $this->mockSimpleActivity($docActivity['id']);

        $this->mockBiz('Activity:ActivityService', array(
            array('functionName' => 'getActivity', 'returnValue' => $activity),
        ));

        $result = $type->isFinished(1);
        $this->assertFalse($result);
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