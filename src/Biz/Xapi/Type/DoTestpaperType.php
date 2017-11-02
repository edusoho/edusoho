<?php

namespace Biz\Xapi\Type;

class DoTestpaperType extends Type
{
    const TYPE = 'do_testpaper';

    public function package($statement)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($statement['target_id']);
        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);
        $course = $this->getCourseService()->getCourse($testpaperResult['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($testpaperResult['courseSetId']);
        $course['description'] = $courseSet['subtitle'];

        $object = array(
            'id' => $testpaperResult['id'],
            'course' => $course,
        );

        $actor = $this->getActor($statement['user_id']);
        $result = array(
            'score' => array(
                'max' => $testpaper['score'],
                'min' => 0,
                'raw' => $testpaperResult['score'],
            ),
        );

        return $this->createXAPIService()->finishTestpaper($actor, $object, $result, false);
    }
}