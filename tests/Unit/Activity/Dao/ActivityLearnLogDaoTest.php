<?php

namespace Tests\Unit\Activity\Dao;

use Biz\Activity\Dao\ActivityDao;
use Tests\Unit\Base\BaseDaoTestCase;

class ActivityLearnLogDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('activityId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('userId' => 1),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('activityId' => 1),
                'expectedResults' => array($expected[0], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('userId' => 1, 'activityId' => 1),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('userId' => 2, 'activityId' => 2),
                'expectedResults' => array(),
                'expectedCount' => 0,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    protected function fetchAndAssembleIds(array $rawInput)
    {
        $res = array();
        foreach ($rawInput as $val) {
            $res[] = $val['id'];
        }

        return $res;
    }

    protected function getSums(array $rawInput)
    {
        $sum = 0;
        foreach ($rawInput as $val) {
            if (is_array($val)) {
                if (isset($val['learnedTime'])) {
                    $sum += $val['learnedTime'];
                } else {
                    throw new \Codeages\Biz\Framework\Dao\DaoException('database table error');
                }
            } elseif (is_numeric($val)) {
                $sum += $val;
            } else {
                throw new \Codeages\Biz\Framework\Dao\DaoException($val);
            }
        }

        return $sum;
    }

    protected function getDefaultMockFields()
    {
        return array(
            'activityId' => 1,
            'userId' => 1,
            'mediaType' => 'video',
            'event' => 'ffff',
            'data' => array('a'),
            'learnedTime' => 1,
            'courseTaskId' => 1,
        );
    }

    private function mockActivity($fields = array())
    {
        $defaultFields = array(
            'title' => 'asdf',
            'remark' => 'asdf',
            'mediaId' => 1,
            'mediaType' => 'video',
            'content' => 'asdf',
            'length' => 1,
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
            'fromUserId' => 1,
            'startTime' => 1,
            'endTime' => 10,
        );

        $fields = array_merge($defaultFields, $fields);

        return $this->getActivityDao()->create($fields);
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
    }
}
