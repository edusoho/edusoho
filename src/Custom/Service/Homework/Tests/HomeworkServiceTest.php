<?php
namespace Custom\Service\Homework\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Announcement\AnnouncementService;
use Topxia\Common\ArrayToolkit;

class HomeworkServiceTest extends BaseTestCase
{
    public function testSample(){
        $this->assertNull(null);
    }

    public function testRandomizeHomeworkResultForPairReview(){
        $sam=$this->getUserService()->register(
            array(
                'nickname'=>'sam', 
                'password'=> '123456',
                'email'=>'sam@geewang.com'
            )
        );

        $zoya=$this->getUserService()->register(
            array(
                'nickname'=>'zoya', 
                'password'=> '123456',
                'email'=>'zoya@geewang.com'
            )
        );

        $course1=$this->getCourseService()->createCourse(
            array(
                'title'=>'course1',
                'type'=>'periodic'
            )
        );
        $this->assertNotEmpty($course1);

        $lesson1=$this->getCourseService()->createLesson(
            array(
                'courseId' => $course1['id'],
                'title' => 'test lesson 1',
                'content' => 'test lesson content 1',
                'type' => 'text'
            )
        );
        $this->assertNotEmpty($lesson1);

        $question1=$this->getQuestionService()->createQuestion(
            array(
                'type' => 'single_choice',
                'stem' => 'test single choice question 1.',
                'choices' => array(
                    'question 1 -> choice 1',
                    'question 1 -> choice 2',
                    'question 1 -> choice 3',
                    'question 1 -> choice 4',
                ),
                'answer' => array(1),
                'target' => 'course-1'
            )
        );
        $this->assertNotEmpty($question1);

        $homework1=$this->getHomeworkService()->createHomework($course1['id'],$lesson1['id'],array(
            'excludeIds'=>"{$question1['id']}",
            'description'=>'helo'
        ));

        // $homeworkItem1=$this->getHomeworkService()->createHomeworkItems($homework1['id'], array(
        //     'questionId'=>$question1['id'],
        //     'score'=>5
        // ));

        $homeworkResult1=$this->getHomeworkService()->startHomework($homework1['id']);
        $homeworkResult1=$this->getHomeworkService()->submitHomework($homeworkResult1['id'],array(
            'questionId'=>$question1['id'],
            'answer'=>$question1['answer']
        ));

        $result = $this->getHomeworkService()->randomizeHomeworkResultForPairReview($homework1['id'], $sam['id']);
        $this->assertNotNull($result);

        // $getedAnnouncement = $this->getAnnouncementService()->getAnnouncement($createdAnnouncement['id']);

        // $this->assertEquals($this->getCurrentUser()->id, $getedAnnouncement['userId']);
        // $this->assertEquals(1, $getedAnnouncement['targetId']);
        // $this->assertEquals('test_announcement', $getedAnnouncement['content']);
    }

    protected function getUserService(){
        return $this->getServiceKernel()->createService('Topxia:User.UserService');
    }

    protected function getHomeworkService(){
        return $this->getServiceKernel()->createService('Custom:Homework.HomeworkService');
    }

    protected function getCourseService(){
        return $this->getServiceKernel()->createService('Custom:Course.CourseService');
    }

    protected function getQuestionService(){
        return $this->getServiceKernel()->createService('Topxia:Question.QuestionService');
    }
}