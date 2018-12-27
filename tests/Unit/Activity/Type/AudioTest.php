<?php

namespace Tests\Unit\Activity\Type;

use AppBundle\Common\ReflectionUtils;

class AudioTest extends BaseTypeTestCase
{
    const TYPE = 'audio';

    public function testCreate()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $audioActivity = $type->create($field);

        $this->assertEquals(1, $audioActivity['mediaId']);
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
        $field = $this->mockField(0);

        $type = $this->getActivityConfig(self::TYPE);
        $type->create($field);
    }

    public function testUpdate()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $audioActivity = $type->create($field);
        $activity = $this->mockSimpleActivity($audioActivity['id']);

        $update = $this->mockField(2);
        $update['mediaId'] = $activity['mediaId'];
        $updated = $type->update($audioActivity['id'], $update, $activity);

        $this->assertEquals(2, $updated['mediaId']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testUpdateWithMediaEmpty()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $audioActivity = $type->create($field);
        $activity = $this->mockSimpleActivity($audioActivity['id']);

        $update = $this->mockField(2);
        unset($update['media']);
        $update['mediaId'] = $activity['mediaId'];
        $type->update($audioActivity['id'], $update, $activity);
    }

    public function testRegisterListeners()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $return = ReflectionUtils::invokeMethod($type, 'registerListeners');

        $this->assertEquals(array('watching' => 'Biz\Activity\Listener\VideoActivityWatchListener'), $return);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testUpdateWithMediaIdEmpty()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $audioActivity = $type->create($field);
        $activity = $this->mockSimpleActivity($audioActivity['id']);

        $update = $this->mockField(0);
        $update['mediaId'] = $activity['mediaId'];
        $type->update($audioActivity['id'], $update, $activity);
    }

    /**
     * @expectedException \Biz\Activity\ActivityException
     * @expectedExceptionMessage exception.activity.not_found
     */
    public function testUpdateWithEmptyActivity()
    {
        $field = $this->mockField();
        $type = $this->getActivityConfig(self::TYPE);
        $audioActivity = $type->create($field);
        $activity = $this->mockSimpleActivity($audioActivity['id'] + 1);

        $update = $this->mockField(2);
        $update['mediaId'] = $activity['mediaId'];
        $type->update($audioActivity['id'], $update, $activity);
    }

    public function testCopy()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $audioActivity = $type->create($field);

        $copy = $type->copy($audioActivity);

        $this->assertEquals($audioActivity['mediaId'], $copy['mediaId']);
    }

    public function testSync()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $field1 = $this->mockField();
        $audioActivity1 = $type->create($field1);
        $activity1 = $this->mockSimpleActivity($audioActivity1['id']);
        $field2 = $this->mockField(3);
        $audioActivity2 = $type->create($field2);
        $activity2 = $this->mockSimpleActivity($audioActivity2['id']);

        $syncedActivity = $type->sync($activity1, $activity2);
        $this->assertNotEquals($audioActivity1['mediaId'], $audioActivity2['mediaId']);

        $this->assertEquals($audioActivity1['mediaId'], $syncedActivity['mediaId']);
    }

    public function testGet()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $audioActivity = $type->create($field);

        $result = $type->get($audioActivity['id']);

        $this->assertEquals($audioActivity['mediaId'], $result['mediaId']);
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
        $audioActivity = $type->create($field);

        $results = $type->find(array($audioActivity['id']));

        $this->assertEquals(1, count($results));
    }

    public function testDelete()
    {
        $field = $this->mockField();

        $type = $this->getActivityConfig(self::TYPE);
        $audioActivity = $type->create($field);
        $pre = $type->get($audioActivity['id']);
        $this->assertFalse(empty($pre['id']));

        $type->delete($audioActivity['id']);
        $result = $type->get($audioActivity['id']);

        $this->assertEmpty($result['file']);
        $this->assertTrue(empty($result['id']));
    }

    /**
     * @param string $source
     * @param string $uri
     * @param int    $mediaId
     *
     * @return array
     */
    private function mockField($mediaId = 1)
    {
        return array(
            'media' => json_encode(array(
                'id' => $mediaId,
            )),
            'content' => 'test content',
            'hasText' => 1,
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
