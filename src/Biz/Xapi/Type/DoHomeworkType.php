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

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        try {
            $homeworkResults = $this->findHomeworkResults(
               array($statements, 'target_id')
            );

            $courses = $this->findCourses(
               array($homeworkResults, 'courseId')
            );

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
                    } else {
                        $result = (object) $result;
                    }

                    $pushStatements[] = $sdk->finishHomework($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
                } catch (\Exception $e) {
                    $this->biz['logger']->error($e);
                }
            }

            return $pushStatements;
        } catch (\Exception $e) {
            $this->biz['logger']->error($e);
        }
    }

    private function findHomeworkResults($subject)
    {
        return $this->find(
            $subject,
            'Testpaper:TestpaperResultDao',
            array('courseId', 'paperName', 'passedStatus')
        );
    }
}
