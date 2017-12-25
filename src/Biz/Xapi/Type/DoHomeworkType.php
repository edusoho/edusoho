<?php

namespace Biz\Xapi\Type;

use AppBundle\Common\ArrayToolkit;

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

    public function packages($statements)
    {
        $homeworkResultIds = ArrayToolkit::column($statements, 'target_id');
        $homeworkResults = $this->getTestpaperService()->findTestpaperResultsByIds($homeworkResultIds);
        $homeworkResults = ArrayToolkit::index($homeworkResults, 'id');

        $courseIds = ArrayToolkit::column($homeworkResults, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');

        $courseSetIds = ArrayToolkit::column($homeworkResults, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
        $courseSets = ArrayToolkit::index($courseSets, 'id');

        foreach ($courses as &$course) {
            $course['description'] = empty($courseSet['subtitle']) ? '' : $courseSet['subtitle'];
            $course['title'] = $courseSet['title'].'-'.$course['title'];
            if (!empty($courseSets[$course['courseSetId']])) {
                $courseSet = $courseSets[$course['courseSetId']];
                $course['description'] = empty($courseSet['subtitle']) ? '' : $courseSet['subtitle'];
                $course['title'] = $courseSet['title'].'-'.$course['title'];
            }
        }

        $sdk = $this->createXAPIService();
        $pushStatements = array();
        foreach ($statements as $statement) {
            try {
                $homeworkResult = $homeworkResults[$statement['target_id']];
                $course = $courses[$homeworkResult['courseId']];
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

                $pushStatements[] = $sdk->finishHomework($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
            } catch (\Exception $e) {
                $this->biz['logger']->error($e);
            }
        }

        return $pushStatements;
    }
}
