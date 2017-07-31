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
        $map =  array(
            'invite-records' => 'AppBundle\Component\Export\InviteRecordsExport',
            'user-invite-records' => 'AppBundle\Component\Export\InviteUserRecordsExport',
            'course-order' => 'AppBundle\Component\Export\Order\CourseOrderExport',
            'classroom-order' => 'AppBundle\Component\Export\Order\ClassroomOrderExport',
            'vip-order' => 'AppBundle\Component\Export\Order\VipOrderExport',
        );
        return $map[$name];
    }

}