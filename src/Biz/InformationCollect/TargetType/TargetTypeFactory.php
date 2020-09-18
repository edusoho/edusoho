<?php

namespace Biz\InformationCollect\TargetType;

class TargetTypeFactory
{
    public function create($targetType)
    {
        $export = self::targetTypeMap($targetType);

        return new $export();
    }

    private function targetTypeMap($name)
    {
        $map = [
            'course' => 'Biz\InformationCollect\TargetType\CourseType',
            'classroom' => 'Biz\InformationCollect\TargetType\ClassroomType',
        ];

        return $map[$name];
    }
}
