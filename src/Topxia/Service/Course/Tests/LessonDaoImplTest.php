<?php
namespace Topxia\Service\Cash\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\ServiceException;


class LessonDaoImplTest extends BaseTestCase
{

    public function testSearchLessons()
    {
        $conditions = array(
            'courseIds' => array(1, 2, 3)
        );
        $orderBy = array('id', 'DESC');
        $start = 0;
        $limit = 10;
       $lessons = $this->getLessonDao()->searchLessons($conditions, $orderBy, $start, $limit);
       $this->assertGreaterThanOrEqual(0, count($lessons));
    }

    protected function getLessonDao()
    {
        return $this->getServiceKernel()->createDao('Course.LessonDao');
    }

}
