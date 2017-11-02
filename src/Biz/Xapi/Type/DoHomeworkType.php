<?php

namespace Biz\Xapi\Type;

class DoHomeworkType extends Type
{
    const TYPE = 'do_homework';

    public function package($statement)
    {
        $homeworkResult = $this->getTestpaperService()->getTestpaperResult($statement['target_id']);
        $course = $this->getCourseService()->getCourse($homeworkResult['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($homeworkResult['courseSetId']);
        $course['description'] = $courseSet['subtitle'];

        $object = array(
            'id' => $homeworkResult['id'],
            'course' => $course,
        );

        $actor = $this->getActor($statement['user_id']);
        $result = array();

        return $this->createXAPIService()->finishHomework($actor, $object, $result, false);
    }
}