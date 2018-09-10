<?php

namespace Tests\Unit\Activity\Type;

use AppBundle\Common\ReflectionUtils;

class VideoTest extends BaseTypeTestCase
{
    const TYPE = 'video';

    public function testCreate()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $videoActivity = $type->create($field);

        $this->assertEquals('self', $videoActivity['mediaSource']);
        $this->assertEquals('end', $videoActivity['finishType']);
        $this->assertEquals(0, $videoActivity['finishDetail']);
        $this->assertEquals(1, $videoActivity['mediaId']);
        $this->assertEmpty($videoActivity['mediaUri']);
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
        $videoActivity = $type->create($field);
        $activity = $this->mockSimpleActivity($videoActivity['id']);

        $update = $this->mockField('end', 1, 'self', '', 2);
        $updated = $type->update($videoActivity['id'], $update, $activity);

        $this->assertEquals(2, $updated['mediaId']);
        $this->assertEquals('self', $updated['mediaSource']);
        $this->assertEquals('end', $updated['finishType']);
        $this->assertEquals(1, $updated['finishDetail']);
        $this->assertEmpty($updated['mediaUri']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testUpdateWithMediaEmpty()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $videoActivity = $type->create($field);
        $activity = $this->mockSimpleActivity($videoActivity['id']);

        $update = $this->mockField();
        unset($update['media']);
        $type->update($videoActivity['id'], $update, $activity);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     * @expectedExceptionMessage finish time can not be empty
     */
    public function testUpdateWithFinishTypeTime()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $videoActivity = $type->create($field);
        $activity = $this->mockSimpleActivity($videoActivity['id']);

        $update = $this->mockField('time', 0, 'self', '', 2);
        $type->update($videoActivity['id'], $update, $activity);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage 教学活动不存在
     */
    public function testUpdateWithEmptyActivity()
    {
        $field = $this->mockField();
        $type = $this->getActivityConfig(self::TYPE);
        $videoActivity = $type->create($field);
        $activity = $this->mockSimpleActivity($videoActivity['id'] + 1);

        $update = $this->mockField('end', 0, 'self', '', 2);
        $type->update($videoActivity['id'], $update, $activity);
    }

    public function testCopy()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $videoActivity = $type->create($field);

        $copy = $type->copy($videoActivity);

        $this->assertEquals($videoActivity['mediaId'], $copy['mediaId']);
        $this->assertEquals($videoActivity['mediaSource'], $copy['mediaSource']);
        $this->assertEquals($videoActivity['finishType'], $copy['finishType']);
        $this->assertEquals($videoActivity['finishDetail'], $copy['finishDetail']);
        $this->assertEquals($videoActivity['mediaUri'], $copy['mediaUri']);
    }

    public function testSync()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $field1 = $this->mockField();
        $videoActivity1 = $type->create($field1);
        $activity1 = $this->mockSimpleActivity($videoActivity1['id']);
        $field2 = $this->mockField('time', 1, 'self', '', 3);
        $videoActivity2 = $type->create($field2);
        $activity2 = $this->mockSimpleActivity($videoActivity2['id']);

        $syncedActivity = $type->sync($activity1, $activity2);

        $this->assertNotEquals($videoActivity1['finishType'], $videoActivity2['finishType']);
        $this->assertNotEquals($videoActivity1['finishDetail'], $videoActivity2['finishDetail']);
        $this->assertNotEquals($videoActivity1['mediaId'], $videoActivity2['mediaId']);

        $this->assertEquals($videoActivity1['finishType'], $syncedActivity['finishType']);
        $this->assertEquals($videoActivity1['finishDetail'], $syncedActivity['finishDetail']);
        $this->assertEquals($videoActivity1['mediaId'], $syncedActivity['mediaId']);
    }

    public function testGet()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $videoActivity = $type->create($field);

        $result = $type->get($videoActivity['id']);

        $this->assertEquals($videoActivity['mediaId'], $result['mediaId']);
        $this->assertEquals($videoActivity['mediaSource'], $result['mediaSource']);
        $this->assertEquals($videoActivity['finishType'], $result['finishType']);
        $this->assertEquals($videoActivity['finishDetail'], $result['finishDetail']);
        $this->assertEquals($videoActivity['mediaUri'], $result['mediaUri']);
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
        $videoActivity = $type->create($field);
        $pre = $type->get($videoActivity['id']);
        $this->assertFalse(empty($pre['id']));

        $type->delete($videoActivity['id']);
        $result = $type->get($videoActivity['id']);
        $this->assertEmpty($result['file']);
        $this->assertTrue(empty($result['id']));
    }

    public function testRegisterListeners()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $return = ReflectionUtils::invokeMethod($type, 'registerListeners');

        $this->assertEquals(array('watching' => 'Biz\Activity\Listener\VideoActivityWatchListener'), $return);
    }

    public function testMaterialSupported()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->assertEquals(true, $type->materialSupported());
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
        );
    }
}
