<?php

namespace Tests\Unit\Activity;

use Biz\BaseTestCase;
use Biz\Course\Dao\CourseMaterialDao;

/**
 * ActivityMaterialCopyTest
 */
class ActivityMaterialCopyTest extends BaseTestCase
{
    public function testDoCopy()
    {
        $source = array();
        $options = array(
            'params' => array(
                'title' => 'course by copy',
            ),
            'originActivity' => array(
                'id' => 1,
                'title' => 'origin activity title',
                'mediaId' => 1,
                'mediaType' => 'video',
                'copyId' => 0,
            ),
            'newActivity' => array(
                'id' => 2,
                'title' => 'new activity title',
                'mediaId' => 2,
                'mediaType' => 'video',
                'copyId' => 1,
            ),
            'newCourse' => array(
                'id' => 2,
                'courseSetId' => 2,
            ),
            'newCourseSet' => array(
                'id' => 2,
                'title' => 'course by copy',
            ),
        );
        $this->mockBiz(
            'Course:CourseMaterialDao',
            array(
                array(
                    'functionName' => 'findMaterialsByLessonIdAndSource',
                    'returnValue' => array(
                        1 => array(
                            'id' => 1,
                            'courseId' => 1,
                            'courseSetId' => 1,
                            'lessonId' => 1,
                            'title' => 'material title',
                            'description' => 'material description',
                            'link' => '',
                            'fileId' => 11,
                            'fileUri' => '',
                            'fileMime' => '',
                            'source' => 'courseactivity',
                            'fileSize' => 100,
                            'userId' => 1,
                            'type' => 'course',
                        ),
                    ),
                    'withParams' => array($options['originActivity']['id'], 'courseactivity'),
                ),
                array(
                    'functionName' => 'batchCreate',
                    'withParams' => array(
                        array(
                            array(
                                'courseId' => 2,
                                'courseSetId' => 2,
                                'lessonId' => 2,
                                'title' => 'material title',
                                'description' => 'material description',
                                'link' => '',
                                'fileId' => 11,
                                'fileUri' => '',
                                'fileMime' => '',
                                'source' => 'courseactivity',
                                'fileSize' => '100',
                                'userId' => 1,
                                'type' => 'course',
                            ),
                        ),
                    ),
                ),
            )
        );
        $this->getActivityMaterialCopy()->doCopy($source, $options);
        $this->getMaterialDao()->shouldHaveReceived('batchCreate')->times(1);
    }

    protected function getActivityMaterialCopy()
    {
        return new \Biz\Activity\Copy\ActivityMaterialCopy($this->biz, array());
    }

    protected function getMaterialDao()
    {
        return $this->createDao('Course:CourseMaterialDao');
    }
}
