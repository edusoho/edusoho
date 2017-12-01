<?php

namespace Biz\Xapi\Type;

class DoHomeworkType extends Type
{
    const TYPE = 'completed_homework';

    public function package($statement)
    {
        $homeworkResult = $this->getTestpaperService()->getTestpaperResult($statement['target_id']);
        $course = $this->getCourseService()->getCourse($homeworkResult['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($homeworkResult['courseSetId']);
        $course['description'] = $courseSet['subtitle'];
        $course['title'] = $courseSet['title'].'-'.$course['title'];

        $object = array(
            'id' => $homeworkResult['id'],
            'name' => $homeworkResult['paperName'],
            'course' => $course,
        );

        $actor = $this->getActor($statement['user_id']);
        $result = array();

        if ('none' != $homeworkResult['passedStatus']) {
            $result['success'] = ('passed' == $homeworkResult['passedStatus']) ? true : false;
        }

        return $this->createXAPIService()->finishHomework($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
    }
}
