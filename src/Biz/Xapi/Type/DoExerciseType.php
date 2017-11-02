<?php

namespace Biz\Xapi\Type;

class DoExerciseType extends Type
{
    const TYPE = 'do_exercise';

    public function package($statement)
    {
        $exerciseFinish = $this->getTestpaperService()->getTestpaperResult($statement['target_id']);
        $course = $this->getCourseService()->getCourse($exerciseFinish['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($exerciseFinish['courseSetId']);
        $course['description'] = $courseSet['subtitle'];

        $object = array(
            'id' => $exerciseFinish['id'],
            'course' => $course,
        );

        $actor = $this->getActor($statement['user_id']);
        $result = array();

        return $this->createXAPIService()->finishExercise($actor, $object, $result, false);
    }
}