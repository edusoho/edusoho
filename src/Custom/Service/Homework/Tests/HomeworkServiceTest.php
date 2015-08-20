<?php
namespace Custom\Service\Homework\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Announcement\AnnouncementService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\CurrentUser;
use Doctrine\DBAL\Query\QueryBuilder;

use Custom\Service\Homework\Dao\Impl\HomeworkResultDaoImpl;

class HomeworkServiceTest extends BaseTestCase
{
    public function testSample(){
        $this->assertNull(null);
    }

    public function testQueryJoin(){
        $user = $this->getUserService()->register(
            array(
                'nickname'=>'user',
                'password'=>'000000',
                'email'=>'123456@qq.com'
            )
        );
        $this->assertNotNull($user);

        $course1=$this->getCourseService()->createCourse(
            array(
                'title'=>'course1',
                'type'=>'periodic'
            )
        );
        $this->assertNotNull($course1);

        $lesson1=$this->getCourseService()->createLesson(
            array(
                'courseId' => $course1['id'],
                'title' => 'test lesson 1',
                'content' => 'test lesson content 1',
                'type' => 'text'
            )
        );
        $this->assertNotNull($lesson1);
        $qb = new QueryBuilder($this->getServiceKernel()->getConnection());

        $qb->select('*')->from('course_lesson', 'l')->join('l', 'course', 'c', 'c.id = l.courseId ');
        $lessons = $qb->execute()->fetchAll();
        $this->assertNotNull($lessons);

    }

    public function testRandomizeHomeworkResultForPairReview(){
        $sam=$this->getUserService()->register(
            array(
                'nickname'=>'sam', 
                'password'=> '123456',
                'email'=>'sam@geewang.com'
            )
        );
        $this->assertNotNull($sam);

        $zoya=$this->getUserService()->register(
            array(
                'nickname'=>'zoya', 
                'password'=> '123456',
                'email'=>'zoya@geewang.com'
            )
        );
        $this->assertNotNull($zoya);

        $tom=$this->getUserService()->register(
            array(
                'nickname'=>'tom', 
                'password'=> '123456',
                'email'=>'tom@geewang.com'
            )
        );
        $this->assertNotNull($tom);

        $bill=$this->getUserService()->register(
            array(
                'nickname'=>'bill', 
                'password'=> '123456',
                'email'=>'bill@geewang.com'
            )
        );
        $this->assertNotNull($bill);

        $course1=$this->getCourseService()->createCourse(
            array(
                'title'=>'course1',
                'type'=>'periodic'
            )
        );
        $this->assertNotNull($course1);

        $lesson1=$this->getCourseService()->createLesson(
            array(
                'courseId' => $course1['id'],
                'title' => 'test lesson 1',
                'content' => 'test lesson content 1',
                'type' => 'text'
            )
        );
        $this->assertNotNull($lesson1);

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
        $this->assertNotNull($question1);

        $homework1=$this->getHomeworkDao()->addHomework(array(
            'courseId' => $course1['id'],
            'lessonId' => $lesson1['id'],
            'minReviews' => 5,
            'pairReview' =>true,
            'completeTime' => strtotime('-1 hours',time()),
            'reviewEndTime' => strtotime('+10 hours',time()),
            'description'=>'helo'
        ));
        $this -> assertNotNull($homework1);

        // $homeworkItem1=$this->getHomeworkService()->createHomeworkItems($homework1['id'], array(
        //     'questionId'=>$question1['id'],
        //     'score'=>5
        // ));

        //
        $homeworkResult1=$this->getResultDao()->addResult(array(
            'homeworkId'=>$homework1['id'],
            'userId' => $this->getServiceKernel()->getCurrentUser()->id,
            'pairReviews' => 5
        ));
        $this -> assertNotNull($homeworkResult1);
        $homeworkResult1=$this->getHomeworkService()->submitHomework($homework1['id'],array(
            'questionId'=>$question1['id']
        ));
        $this -> assertNotNull($homeworkResult1);
        $homeworkResult1=$this->getResultDao()->updateResult($homeworkResult1['id'],array(
            'status' =>'pairReviewing'
        ));
        $this -> assertNotNull($homeworkResult1);

        //
        $currentUser = new CurrentUser();
        $currentUser->fromArray($sam);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $homeworkResult2=$this->getResultDao()->addResult(array(
            'homeworkId'=>$homework1['id'],
            'userId' => $sam['id']
        ));
        $homeworkResult2=$this->getHomeworkService()->submitHomework($homework1['id'],array(
            'questionId'=>$question1['id']
        ));
        $homeworkResult2=$this->getResultDao()->updateResult($homeworkResult1['id'],array(
            'status' =>'pairReviewing'
        ));
        $this -> assertNotNull($homeworkResult2);

        $currentUser = new CurrentUser();
        $currentUser->fromArray($zoya);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $homeworkResult3=$this->getResultDao()->addResult(array(
            'homeworkId'=>$homework1['id'],
            'userId' => $zoya['id']
        ));
        $homeworkResult3=$this->getHomeworkService()->submitHomework($homework1['id'],array(
            'questionId'=>$question1['id']
        ));
        $homeworkResult3=$this->getResultDao()->updateResult($homeworkResult1['id'],array(
            'status' =>'pairReviewing'
        ));
        $this -> assertNotNull($homeworkResult3);

        $currentUser = new CurrentUser();
        $currentUser->fromArray($tom);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $homeworkResult4=$this->getResultDao()->addResult(array(
            'homeworkId'=>$homework1['id'],
            'userId' => $tom['id']
        ));
        $homeworkResult4=$this->getHomeworkService()->submitHomework($homework1['id'],array(
            'questionId'=>$question1['id']
        ));
        $homeworkResult4=$this->getResultDao()->updateResult($homeworkResult1['id'],array(
            'status' =>'pairReviewing'
        ));
        $this -> assertNotNull($homeworkResult4);
        $this->getHomeworkService()->createHomeworkPairReview($homeworkResult4['id'],$tom['id'], array(
            'score'=>3
        ));

        //bill自己的作业
        $currentUser = new CurrentUser();
        $currentUser->fromArray($bill);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $homeworkResult5=$this->getResultDao()->addResult(array(
            'homeworkId'=>$homework1['id'],
            'userId' => $bill['id']
        ));
        $homeworkResult5=$this->getHomeworkService()->submitHomework($homework1['id'],array(
            'questionId'=>$question1['id']
        ));
        $homeworkResult5=$this->getResultDao()->updateResult($homeworkResult1['id'],array(
            'status' =>'pairReviewing'
        ));

        //为bill分配一份作业，自己的作业不能被分配到，本人已经互评的作业不能被分配到，在存在未达最小互评数的答卷时，已经达到互评数要求的不能被分配到.
        $result = $this->getHomeworkService()->randomizeHomeworkResultForPairReview($homework1['id'], $bill['id']);
        $this->assertNotNull($result);
        $this ->assertContains($result['id'], array($homeworkResult2['id'], $homeworkResult3['id']));
    }

    public function testForwardHomeworkStatus(){
        $user=$this->getServiceKernel()->getCurrentUser();
        $homework1=$this->getHomeworkDao()->addHomework(array('completeTime'=>strtotime('-1 hours', time()),'pairReview'=>true));
        $homework2=$this->getHomeworkDao()->addHomework(array('completeTime'=>strtotime('+1 hours', time()),'pairReview'=>true));
        $result1=$this->getResultDao()->addResult(array('userId'=>$user->id,'homeworkId'=>$homework1['id'],'status'=>'editing'));
        $result2=$this->getResultDao()->addResult(array('userId'=>$user->id,'homeworkId'=>$homework1['id'],'status'=>'finished'));
        $result3=$this->getResultDao()->addResult(array('userId'=>$user->id,'homeworkId'=>$homework2['id'],'status'=>'editing'));

        $this->getHomeworkService()->forwardHomeworkStatus();
        $l1=$this->getResultDao()->getResult($result1['id']);
        $this->assertEquals('pairReviewing',$l1['status']);
        $l2=$this->getResultDao()->getResult($result2['id']);
        $this->assertEquals('finished',$l2['status']);
        $l3=$this->getResultDao()->getResult($result3['id']);
        $this->assertEquals('editing',$l3['status']);
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

    protected function getHomeworkDao(){
        return $this->getServiceKernel()->createDao('Homework:Homework.HomeworkDao');
    }

    protected function getResultDao(){
        return $this->getServiceKernel()->createDao('Custom:Homework.ResultDao');
    }
}