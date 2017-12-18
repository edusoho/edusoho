<?php

namespace Biz\Xapi\Type;

class DoExerciseType extends Type
{
    const TYPE = 'completed_exercise';

    public function package($statement)
    {
        $exerciseFinish = $this->getTestpaperService()->getTestpaperResult($statement['target_id']);
        $course = $this->getCourseService()->getCourse($exerciseFinish['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($exerciseFinish['courseSetId']);
        $course['description'] = $courseSet['subtitle'];
        $course['title'] = $courseSet['title'].'-'.$course['title'];

        $object = array(
            'id' => $exerciseFinish['id'],
            'name' => $exerciseFinish['paperName'],
            'course' => $course,
        );

        $actor = $this->getActor($statement['user_id']);
        $result = array();

        return $this->createXAPIService()->finishExercise($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
    }

    public function packages($statements)
    {
        // TODO: Implement packages() method.
    }
}
