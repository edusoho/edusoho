<?php

namespace Biz\Course\Component\Clones\Chain;

use Biz\Course\Component\Clones\AbstractClone;
use Codeages\Biz\Framework\Context\Biz;

class CourseClone extends AbstractClone
{
    public function __construct(Biz $biz, array $processNodes = array(), $auto = false)
    {
        $processNodes = array(
            'course-member' => array(
                'class' => 'Biz\Course\Component\Clones\Chain\CourseMemberClone',
                'priority' => 100,
            ),
//            'course-question' =>
            'course-testpaper' => array(
                'class' => 'Biz\Course\Component\Clones\Chain\CourseTestpaperClone',
                'priority' => 100,
            ),
            'course-task' => array(
                'class' => 'Biz\Course\Component\Clones\Chain\CourseTaskClone',
            ),
        );
        parent::__construct($biz, $processNodes, $auto);
    }

    protected function cloneEntity($source, $options)
    {
        $this->doCourseCloneProcess($source, $options);
    }

    private function doCourseCloneProcess($source, $options)
    {
        foreach ($this->processNodes as $processNode) {
            $class = new $processNode['class']($this->biz);
            $class->clones($source, $options);
        }
    }

    protected function getFields()
    {
        // TODO: Implement getFields() method.
    }
}
