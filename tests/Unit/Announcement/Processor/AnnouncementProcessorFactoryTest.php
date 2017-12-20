<?php

namespace Tests\Unit\Announcement\Processor;

use Biz\BaseTestCase;
use Biz\Announcement\Processor\AnnouncementProcessorFactory;

class AnnouncementProcessorFactoryTest extends BaseTestCase
{
    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateWithEmptyTarget()
    {
        $processor = new AnnouncementProcessorFactory($this->getBiz());
        $processor->create(array());
    }

    public function testCreate()
    {
        $processor = new AnnouncementProcessorFactory($this->getBiz());
        $result = $processor->create('course');
        $className = get_class($result);

        $this->assertEquals('Biz\Announcement\Processor\CourseAnnouncementProcessor', $className);
    }
}
