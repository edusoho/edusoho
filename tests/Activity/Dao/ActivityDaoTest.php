<?php

namespace Tests\Activity\Dao;

use Biz\BaseTestCase;

class ActivityDaoTest extends BaseTestCase
{
	public function testFindByCourseId()
	{
		$activity = $this->mockActivity('activity 1');
		$activities = $this->getActivityService()->findByCourseId(1);
		$this->assertEquals(count($activities), 1);
		$this->assertArrayEquals($activity, $activities[0], array(
			'title', 
			'mediaId',
			'mediaType',
			'content',
			'fromCourseId',
			'fromCourseSetId',
			'fromUserId'
		));
	}

	public function testFindByIds()
	{
		$activity1 = $this->mockActivity('activity 1');
		$activity2 = $this->mockActivity('activity 2');
		$activity3 = $this->mockActivity('activity 3');

		$activities = $this->getActivityService()->findByIds(array($activity1['id'], $activity2['id'], $activity3['id']));
		$this->assertEquals(count($activities), 3);

		$this->assertArrayEquals($activity1, $activities[0], array(
			'title', 
			'mediaId',
			'mediaType',
			'content',
			'fromCourseId',
			'fromCourseSetId',
			'fromUserId'
		));

		$this->assertArrayEquals($activity2, $activities[1], array(
			'title', 
			'mediaId',
			'mediaType',
			'content',
			'fromCourseId',
			'fromCourseSetId',
			'fromUserId'
		));

		$this->assertArrayEquals($activity3, $activities[2], array(
			'title', 
			'mediaId',
			'mediaType',
			'content',
			'fromCourseId',
			'fromCourseSetId',
			'fromUserId'
		));
	}

	private function mockActivity($title)
	{
		$fields = array(
			'title' => $title,
			'mediaId' => 0,
			'mediaType' => 'text',
			'content' => '124',
			'fromCourseId' => 1,
			'fromCourseSetId' => 1,
			'fromUserId' => 1,
		);
		return $this->getActivityService()->create($fields);
	}

	protected function getActivityService()
    {
        return $this->getBiz()->dao('Activity:ActivityDao');
    }
}