<?php

namespace Biz\Xapi\Type;

use AppBundle\Common\ArrayToolkit;

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
        $result = array('success' => true);

        return $this->createXAPIService()->finishExercise($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
    }

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        try {
            $exerciseResultIds = ArrayToolkit::column($statements, 'target_id');
            $exerciseResults = $this->getTestpaperService()->findTestpaperResultsByIds($exerciseResultIds);
            $exerciseResults = ArrayToolkit::index($exerciseResults, 'id');

            $courseIds = ArrayToolkit::column($exerciseResults, 'courseId');
            $courses = $this->getCourseService()->findCoursesByIds($courseIds);
            $courses = ArrayToolkit::index($courses, 'id');

            $courseSetIds = ArrayToolkit::column($exerciseResults, 'courseSetId');
            $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
            $courseSets = ArrayToolkit::index($courseSets, 'id');

            foreach ($courses as &$course) {
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
                    $exerciseResult = $exerciseResults[$statement['target_id']];
                    $course = $courses[$exerciseResult['courseId']];
                    $object = array(
                        'id' => $exerciseResult['id'],
                        'name' => $exerciseResult['paperName'],
                        'course' => $course,
                    );

                    $actor = $this->getActor($statement['user_id']);
                    $result = array('success' => true);

                    $pushStatements[] = $sdk->finishExercise($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
                } catch (\Exception $e) {
                    $this->biz['logger']->error($e);
                }
            }

            return $pushStatements;
        } catch (\Exception $e) {
            $this->biz['logger']->error($e);
        }
    }
}
