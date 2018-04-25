<?php

namespace Biz\Xapi\Type;

use AppBundle\Common\ArrayToolkit;

class DoTestpaperType extends Type
{
    const TYPE = 'completed_testpaper';

    public function packages($statements)
    {
        if (empty($statements)) {
            return array();
        }
        try {
            $testpaperResultIds = ArrayToolkit::column($statements, 'target_id');
            $testpaperResults = $this->getTestpaperService()->findTestpaperResultsByIds($testpaperResultIds);
            $testpaperResults = ArrayToolkit::index($testpaperResults, 'id');

            $testpaperIds = ArrayToolkit::column($testpaperResults, 'testId');
            $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);
            $testpapers = ArrayToolkit::index($testpapers, 'id');

            $courses = $this->findCourses(
                array($testpaperResults, 'courseId')
            );

            $sdk = $this->createXAPIService();
            $pushStatements = array();

            foreach ($statements as $statement) {
                try {
                    $testpaperResult = $testpaperResults[$statement['target_id']];
                    $course = $courses[$testpaperResult['courseId']];
                    $testpaper = $testpapers[$testpaperResult['testId']];
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

                    $pushStatements[] = $sdk->finishTestpaper($actor, $object, $result, $statement['uuid'], $statement['occur_time'], false);
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
