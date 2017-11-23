<?php

namespace Biz\Xapi\Type;

class DoTestpaperType extends Type
{
    const TYPE = 'completed_testpaper';

    public function package($statement)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($statement['target_id']);
        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);
        $course = $this->getCourseService()->getCourse($testpaperResult['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($testpaperResult['courseSetId']);
        $course['description'] = $courseSet['subtitle'];
        $course['title'] = $courseSet['title'].'-'.$course['title'];

        $object = array(
            'id' => $testpaperResult['id'],
            'name' => $testpaperResult['paperName'],
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

        if ('none' != $testpaperResult['passedStatus']) {
            $result['success'] = ('passed' == $testpaperResult['passedStatus']) ? true : false;
        }

        return $this->createXAPIService()->finishTestpaper($actor, $object, $result, $statement['uuid'], $statement['created_time'], false);
    }
}
