<?php

namespace Tests\Unit\Marker\Service;

use Biz\BaseTestCase;
use Biz\Marker\Service\MarkerService;
use Biz\User\CurrentUser;

class MarkerServiceTest extends BaseTestCase
{
    /**
     * @expectedException \Biz\Marker\MarkerException
     * @expectedExceptionMessage exception.marker.field_second_required
     */
    public function testAddMarkerException()
    {
        $this->mockUploadFile(1);
        $this->getMarkerService()->addMarker(1, array('second' => ''));
    }

    public function testAddMarker()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->getQuestionService()->create($arguments);
        $marker = $this->getMarkerService()->addMarker(1, $fields);
        $this->assertEquals($marker['markerId'], 1);
        $this->assertEquals($marker['questionId'], 1);

        return $marker;
    }

    public function testGetMarker()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->getQuestionService()->create($arguments);
        $marker = $this->getMarkerService()->addMarker(1, $fields);
        $marker = $this->getMarkerService()->getMarker($marker['id']);
        $this->assertEquals($marker['mediaId'], 0);
        $this->assertEquals($marker['second'], 30);

        return $marker;
    }

    public function testGetMarkersByIds()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->getQuestionService()->create($arguments);
        $this->getMarkerService()->addMarker(1, $fields);
        $this->getMarkerService()->addMarker(3, $fields);
        $markers = $this->getMarkerService()->getMarkersByIds(array(1, 2));
        $this->assertEquals($markers[1]['mediaId'], 0);
        $this->assertEquals($markers[2]['mediaId'], 0);

        return $markers;
    }

    public function testFindMarkersByMediaId()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->mockUploadFile(1);
        $this->getQuestionService()->create($arguments);
        $this->getMarkerService()->addMarker(1, $fields);
        $this->mockUploadFile(2);
        $this->getMarkerService()->addMarker(2, $fields);

        $result = $this->getMarkerService()->findMarkersByMediaId(1);
        $this->assertCount(1, $result);

        $result = $this->getMarkerService()->findMarkersByMediaId(122313);
        $this->assertEmpty($result);
    }

    public function testFIndMarkersMetaByMediaId()
    {
        $result = $this->getMarkerService()->findMarkersMetaByMediaId(231232112);
        $this->assertEmpty($result);

        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->mockUploadFile(1);
        $this->getQuestionService()->create($arguments);
        $this->getMarkerService()->addMarker(1, $fields);
        $this->mockUploadFile(2);
        $this->getMarkerService()->addMarker(2, $fields);

        $result = $this->getMarkerService()->findMarkersMetaByMediaId(1);
        $this->assertCount(1, $result);
    }

    public function testSearchMarkers()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->getQuestionService()->create($arguments);
        $this->getMarkerService()->addMarker(1, $fields);
        $this->getMarkerService()->addMarker(3, $fields);
        $this->getMarkerService()->addMarker(3, $fields);
        $conditions = array(
            'mediaId' => 0,
        );
        $markers = $this->getMarkerService()->searchMarkers($conditions, array('createdTime' => 'DESC'), 0, 10);
        $this->assertEquals($markers[0]['mediaId'], 0);

        return $markers;
    }

    /**
     * @expectedException \Biz\Marker\MarkerException
     * @expectedExceptionMessage exception.marker.not_found
     */
    public function testUpdateMarkerNotFoundException()
    {
        $this->getMarkerService()->updateMarker(1, array());
    }

    /**
     * @expectedException \Biz\Marker\MarkerException
     * @expectedExceptionMessage exception.marker.field_second_required
     */
    public function testUpdateMarkerFieldSecondRequiredException()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->getQuestionService()->create($arguments);
        $this->getMarkerService()->addMarker(1, $fields);

        $this->getMarkerService()->updateMarker(1, array());
    }

    public function testUpdateMarker()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->getQuestionService()->create($arguments);
        $marker1 = $this->getMarkerService()->addMarker(1, $fields);
        $marker1 = $this->getMarkerService()->getMarker($marker1['markerId']);

        $this->assertEquals($marker1['second'], 30);
        $fields = array(
            'second' => 20,
            'updatedTime' => time(),
        );
        $marker2 = $this->getMarkerService()->updateMarker($marker1['id'], $fields);
        $this->assertEquals($marker2['second'], 20);

        return $marker2;
    }

    /**
     * @expectedException \Biz\Marker\MarkerException
     * @expectedExceptionMessage exception.marker.not_found
     */
    public function testDeleteMarkerNotFoundException()
    {
        $this->getMarkerService()->deleteMarker(1);
    }

    public function testDeleteMarker()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->getQuestionService()->create($arguments);
        $marker1 = $this->getMarkerService()->addMarker(1, $fields);
        $marker1 = $this->getMarkerService()->getMarker($marker1['markerId']);
        $this->assertEquals($marker1['second'], 30);
        $marker = $this->getMarkerService()->deleteMarker($marker1['id']);
        $this->assertEquals($marker, true);

        return $marker;
    }

    public function testIsFinishMarker()
    {
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );
        $arguments = array(
            'type' => 'single_choice',
            'parentId' => 0,
            'stem' => '111',
            'answer' => array(1),
            'choices' => array(1, 2, 3, 4),
            'target' => 'course-1',
            'courseSetId' => 1,
        );

        $this->getQuestionService()->create($arguments);
        $marker = $this->getMarkerService()->addMarker(1, $fields);

        $this->mockBiz('Marker:QuestionMarkerService', array(
            array(
                'functionName' => 'findQuestionMarkersByMarkerId',
                'returnValue' => array(),
            ),
        ));
        $result = $this->getMarkerService()->isFinishMarker(1, $marker['id']);
        $this->assertTrue($result);

        $this->mockBiz('Marker:QuestionMarkerService', array(
            array(
                'functionName' => 'findQuestionMarkersByMarkerId',
                'returnValue' => array(array('id' => $marker['id'])),
            ),
        ));

        $this->mockBiz('Marker:QuestionMarkerResultService', array(
            array(
                'functionName' => 'findByUserIdAndMarkerId',
                'returnValue' => array(
                    array(
                        'status' => 'none',
                        'questionMarkerId' => $marker['id'],
                    ),
                ),
            ),
        ));

        $result = $this->getMarkerService()->isFinishMarker(1, $marker['id']);
        $this->assertFalse($result);

        $this->mockBiz('Marker:QuestionMarkerService', array(
            array(
                'functionName' => 'findQuestionMarkersByMarkerId',
                'returnValue' => array(array('id' => $marker['id'])),
            ),
        ));

        $this->mockBiz('Marker:QuestionMarkerResultService', array(
            array(
                'functionName' => 'findByUserIdAndMarkerId',
                'returnValue' => array(
                    array(
                        'status' => 'right',
                        'questionMarkerId' => 123213,
                    ),
                ),
            ),
        ));

        $result = $this->getMarkerService()->isFinishMarker(1, $marker['id']);
        $this->assertFalse($result);

        $this->mockBiz('Marker:QuestionMarkerService', array(
            array(
                'functionName' => 'findQuestionMarkersByMarkerId',
                'returnValue' => array(array('id' => $marker['id'])),
            ),
        ));

        $this->mockBiz('Marker:QuestionMarkerResultService', array(
            array(
                'functionName' => 'findByUserIdAndMarkerId',
                'returnValue' => array(
                    array(
                        'status' => 'right',
                        'questionMarkerId' => $marker['id'],
                    ),
                ),
            ),
        ));

        $result = $this->getMarkerService()->isFinishMarker(1, $marker['id']);
        $this->assertTrue($result);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.unlogin
     */
    public function testCanManageMarkerUnLoginException()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1),
        ));

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getMarkerService()->canManageMarker(1);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.permission_denied
     */
    public function testCanManageMarkerPermissionDenyException()
    {
        $this->getMarkerService()->canManageMarker(233);
    }

    /**
     * @expectedException \Biz\System\SettingException
     * @expectedExceptionMessage exception.setting.cloud_video_disable
     */
    public function testCanManageMarkerCloudVideoDisableException()
    {
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('upload_mode' => 'local'),
            ),
        ));

        $this->getMarkerService()->canManageMarker($this->getCurrentUser()->getId());
    }

    public function testCanManageMarker()
    {
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('upload_mode' => 'cloud'),
            ),
        ));

        $result = $this->getMarkerService()->canManageMarker($this->getCurrentUser()->getId());
        $this->assertTrue($result);
    }

    public function testMerge()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question1 = array(
            'type' => 'choice',
            'stem' => 'adealllll',
            'difficulty' => 'difficult',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $question = $this->getQuestionService()->create($question);
        $question1 = $this->getQuestionService()->create($question1);
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $questionMarker = $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker1 = $this->getMarkerService()->addMarker(2, $fields);

        $result = $this->getMarkerService()->merge($questionMarker['markerId'], $questionMarker1['markerId']);
        $this->assertTrue($result);

        $marker = $this->getMarkerService()->getMarker($questionMarker['markerId']);
        $this->assertEmpty($marker);
    }

    private function createCourse($customFields = array())
    {
        $defaultFields = array(
            'title' => 'test-create-course',
            'courseSetId' => 1,
            'learnMode' => 'freeMode',
            'expiryMode' => 'forever',
            'expiryStartDate' => '',
            'expiryEndDate' => '',
        );

        $fields = array_merge($defaultFields, $customFields);

        return $this->getCourseService()->createCourse($fields);
    }

    private function mockUploadFile($id)
    {
        $this->mockBiz('File:UploadFileService', array(
            array(
                'functionName' => 'getFile',
                'returnValue' => array(
                    'id' => $id,
                ),
            ),
        ));
    }

    /**
     * @return MarkerService
     */
    protected function getMarkerService()
    {
        return $this->createService('Marker:MarkerService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
