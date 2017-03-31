<?php

namespace Biz\Course\CourseProcessor;

use Codeages\Biz\Framework\Context\Biz;

abstract class BaseCourseProcessor
{
    /**
     * @var Biz
     */
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    protected function getBiz()
    {
        return $this->biz;
    }
}
