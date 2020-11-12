<?php

namespace Tests\Unit\Visualization\Dao;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\CoursePlanLearnDailyDao;
use Biz\Visualization\Dao\Impl\CoursePlanLearnDailyDaoImpl;
use Biz\Visualization\Dao\Impl\UserLearnDailyDaoImpl;
use Biz\Visualization\Dao\UserLearnDailyDao;
use Biz\Visualization\Dao\UserStayDailyDao;

class CoursePlanLearnDailyDaoTest extends BaseTestCase
{
    public function testGet()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $created = $this->getCoursePlanLearnDailyDao()->create($defaultMockFields);

        $result = $this->getCoursePlanLearnDailyDao()->get($created['id']);

        self::assertNotNull($result);
        self::assertEquals($result['userId'], $defaultMockFields['userId']);
    }

    protected function getDefaultMockFields()
    {
        return [
            'userId' => 3,
            'courseId' => 1,
            'courseSetId' => 1,
            'dayTime' => time(),
            'sumTime' => 10,
            'pureTime' => 10,
        ];
    }

    /**
     * @return CoursePlanLearnDailyDao
     */
    protected function getCoursePlanLearnDailyDao()
    {
        return $this->biz->dao('Visualization:CoursePlanLearnDailyDao');
    }
}
