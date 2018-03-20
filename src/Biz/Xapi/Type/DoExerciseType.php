<?php

namespace Biz\Xapi\Type;

class DoExerciseType extends Type
{
    const TYPE = 'completed_exercise';

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        try {
            $exerciseResults = $this->findExerciseResults(
                array($statements, 'target_id')
            );

            $courses = $this->findCourses(
                array($exerciseResults, 'courseId')
            );

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

    private function findExerciseResults($subject)
    {
        return $this->find(
            $subject,
            'Testpaper:TestpaperResultDao',
            array('courseId', 'paperName')
        );
    }
}
