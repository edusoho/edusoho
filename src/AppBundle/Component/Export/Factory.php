<?php

namespace AppBundle\Component\Export;

class Factory
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function create($name, $conditions)
    {
        $export = $this->exportMap($name);

        return new $export($this->container, $conditions);
    }

    private function exportMap($name)
    {
        $map = array(
            'invite-records' => 'AppBundle\Component\Export\InviteRecordsExporter',
            'user-invite-records' => 'AppBundle\Component\Export\InviteUserRecordsExporter',
            'course-order' => 'AppBundle\Component\Export\Order\CourseOrderExporter',
            'classroom-order' => 'AppBundle\Component\Export\Order\ClassroomOrderExporter',
            'vip-order' => 'AppBundle\Component\Export\Order\VipOrderExporter',
            'course-overview-student-list' => 'AppBundle\Component\Export\Course\OverviewStudentExporter',
        );

        return $map[$name];
    }
}
