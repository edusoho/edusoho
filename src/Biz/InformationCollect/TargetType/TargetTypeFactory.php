<?php

namespace Biz\InformationCollect\TargetType;

use Codeages\Biz\Framework\Context\Biz;

class TargetTypeFactory
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function create($targetType)
    {
        $export = self::targetTypeMap($targetType);

        return new $export($this->biz);
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
