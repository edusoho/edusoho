<?php

namespace Tests\Activity\Dao;

use Tests\Base\BaseDaoTestCase;

class ActivityDaoTest extends BaseDaoTestCase
{
	public function testSearch()
	{
		$activity = $this->mockActivity(array('title' => 'activity 1'));
		$this->searchTestUtil($this->getActivityDao(), $activity, array(
			'title', 
			'mediaId',
			'mediaType',
			'content',
			'fromCourseId',
			'fromCourseSetId',
			'fromUserId'
		));
	}

	public function testFindByCourseId()
	{
		$activity = $this->mockActivity(array('title' => 'activity 1'));
		$activities = $this->getActivityDao()->findByCourseId(1);
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
		$activity1 = $this->mockActivity(array('title' => 'activity 1'));
		$activity2 = $this->mockActivity(array('title' => 'activity 2'));
		$activity3 = $this->mockActivity(array('title' => 'activity 3'));

		$activities = $this->getActivityDao()->findByIds(array($activity1['id'], $activity2['id'], $activity3['id']));
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

	private function mockActivity($fields)
	{
		$defaultFields = array(
			'title' => 'activity',
			'mediaId' => 0,
			'mediaType' => 'text',
			'content' => '124',
			'fromCourseId' => 1,
			'fromCourseSetId' => 1,
			'fromUserId' => 1,
		);

		$fields = array_merge($fields, $defaultFields);
		return $this->getActivityDao()->create($fields);
	}

	protected function getActivityDao()
    {
        return $this->getBiz()->dao('Activity:ActivityDao');
    }
}