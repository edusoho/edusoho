<?php

namespace Tests\Unit\S2B2C\Sync\Component;

use Biz\BaseTestCase;

class CourseSetQuestionSyncTest extends BaseTestCase
{
    public function testSync()
    {
        $file = $this->getContainer()->getParameter('kernel.root_dir').'/../tests/Unit/S2B2C/Fixtures/testpaper_only_sync.json';
        $data = json_decode(file_get_contents($file), true);
        $this->biz['s2b2c.course_product_sync']->sync($data);
    }
}
