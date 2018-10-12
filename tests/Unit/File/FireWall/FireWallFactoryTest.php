<?php

namespace Tests\Unit\File\FireWall;

use Biz\BaseTestCase;
use Biz\File\FireWall\FireWallFactory;

class FireWallFactoryTest extends BaseTestCase
{
    public function testCreate()
    {
        $fireWallFactory = new FireWallFactory($this->biz);
        $parameter = null;
        $fireWalls = array(
            'course.test',
            'classroom.test',
            'article.test',
            'group.test',
            'question.test',
            'course',
        );

        foreach ($fireWalls as $fireWall) {
            $fireWall = $fireWallFactory->create($fireWall);
            $this->assertNotNull($fireWall);
        }
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testCreateException()
    {
        $fireWallFactory = new FireWallFactory($this->biz);
        $parameter = null;
        $fireWallFactory->create($parameter);
    }
}
