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
            'courseId'=>$homework1['courseId'],
            'lessonId' => $homework1['lessonId'],
            'userId' => $this->getServiceKernel()->getCurrentUser()->id,
            'pairReviews' => 5,
            'status' =>'pairReviewing'
        ));
        $this -> assertNotNull($homeworkResult1);

        //
        $homeworkResult2=$this->getResultDao()->addResult(array(
            'homeworkId'=>$homework1['id'],
            'courseId'=>$homework1['courseId'],
            'lessonId' => $homework1['lessonId'],
            'userId' => $sam['id'],
            'status' =>'pairReviewing'
        ));
        $this -> assertNotNull($homeworkResult2);

        $homeworkResult3=$this->getResultDao()->addResult(array(
            'homeworkId'=>$homework1['id'],
            'courseId'=>$homework1['courseId'],
            'lessonId' => $homework1['lessonId'],
            'userId' => $zoya['id'],
            'status' =>'pairReviewing'
        ));
        $this -> assertNotNull($homeworkResult3);

        $homeworkResult4=$this->getResultDao()->addResult(array(
            'homeworkId'=>$homework1['id'],
            'courseId'=>$homework1['courseId'],
            'lessonId' => $homework1['lessonId'],
            'userId' => $tom['id'],
            'status' =>'pairReviewing'
        ));

        $this->getReviewDao()->create(array(
            'category' =>'student',
            'homeworkId' =>$homework1['id'],
            'homeworkResultId'=>$homeworkResult4['id'],
            'userId'=>$bill['id'],
            'score'=>3
        ));

        //bill自己的作业
        $currentUser = new CurrentUser();
        $currentUser->fromArray($bill);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $homeworkResult5=$this->getResultDao()->addResult(array(
            'homeworkId'=>$homework1['id'],
            'courseId'=>$homework1['courseId'],
            'lessonId' => $homework1['lessonId'],
            'userId' => $bill['id'],
            'status' =>'pairReviewing'
        ));

        //为bill分配一份作业，自己的作业不能被分配到，本人已经互评的作业不能被分配到，在存在未达最小互评数的答卷时，已经达到互评数要求的不能被分配到.
        $result = $this->getHomeworkService()->randomizeHomeworkResultForPairReview($homework1['id'], $bill['id']);
        $this->assertNotNull($result);
        $this ->assertContains($result['id'], array($homeworkResult2['id'], $homeworkResult3['id']));
    }

    public function testCreateHomeworkReview(){
        $user=$this->getServiceKernel()->getCurrentUser();
        $homework1=$this->getHomeworkDao()->addHomework(array('completeTime'=>strtotime('-1 hours', time()),'pairReview'=>true));
        $result1=$this->getResultDao()->addResult(array('userId'=>$user->id,'homeworkId'=>$homework1['id'],'status'=>'editing'));
        $result1Item1=$this->getResultItemDao()->addItemResult(array(
            'itemId'=>1,
            'homeworkResultId'=>$result1['id'],
        ));
        $this->assertNull($result1Item1['score']);
        $result1Item2=$this->getResultItemDao()->addItemResult(array(
            'itemId'=>2,
            'homeworkResultId'=>$result1['id'],
        ));

        $review = $this->getHomeworkService()->createHomeworkReview($result1['id'], $user->id, array(
            'category' => 'teacher',
            'items' => array(
                array(
                    'homeworkItemResultId' => $result1Item1['id'],
                    'score' => 5,
                    'review' => 'review TEst'
                ),
                array(
                    'homeworkItemResultId' => $result1Item2['id'],
                    'score' => 3,
                    'review' => 'review 55555'
                )
            )
        ));
        $this->assertNotNull($review);
        $this->assertNotNull($review['id']);
        $i1 = $this->getHomeworkService()->loadHomeworkResultItem($result1Item1['id']);
        $this->assertNotNull($i1);

        $this->assertEquals(5,$i1['score']);
        $items=$this->getHomeworkService()->getIndexedReviewItems($result1['id']);
        $this->assertEquals('review TEst',$items[$result1Item1['id']]['teacher'][0]['review']);

    }

    public function testForwardHomeworkStatusForEditingHomeworks(){
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

    public function testForwardHomeworkStatusForFinishingHomeworks(){
        $user=$this->getServiceKernel()->getCurrentUser();
        $homework1=$this->getHomeworkDao()->addHomework(array('minReviews'=>2,'completePercent'=>1.0,'reviewEndTime'=>strtotime('-1 hours', time()),'pairReview'=>true));
        $homework2=$this->getHomeworkDao()->addHomework(array('reviewEndTime'=>strtotime('+1 hours', time()),'pairReview'=>true));
        $result1=$this->getResultDao()->addResult(array('userId'=>$user->id,'homeworkId'=>$homework1['id'],'status'=>'pairReviewing'));
        $result1Item1=$this->getResultItemDao()->addItemResult(array(
            'itemId'=>1,
            'homeworkResultId'=>$result1['id'],
        ));
        $result1Item2=$this->getResultItemDao()->addItemResult(array(
            'itemId'=>2,
            'homeworkResultId'=>$result1['id'],
        ));
        $result1review1=$this->getReviewDao()->create(array(
            'homeworkResultId'=>$result1['id'],
            'homeworkId'=>$homework1['id'],
            'category'=>'student'
        ));
        $this->getReviewItemDao()->create(array(
            'homeworkResultId'=>$result1['id'],
            'homeworkItemResultId'=>$result1Item1['id'],
            'homeworkReviewId'=>$result1review1['id'],
            'score'=>8
        ));
        $this->getReviewItemDao()->create(array(
            'homeworkResultId'=>$result1['id'],
            'homeworkItemResultId'=>$result1Item2['id'],
            'homeworkReviewId'=>$result1review1['id'],
            'score'=>2
        ));
        $result1review2=$this->getReviewDao()->create(array(
            'homeworkResultId'=>$result1['id'],
            'homeworkId'=>$homework1['id'],
            'category'=>'student'
        ));
        $this->getReviewItemDao()->create(array(
            'homeworkResultId'=>$result1['id'],
            'homeworkItemResultId'=>$result1Item1['id'],
            'homeworkReviewId'=>$result1review2['id'],
            'score'=>10
        ));
        $this->getReviewItemDao()->create(array(
            'homeworkResultId'=>$result1['id'],
            'homeworkItemResultId'=>$result1Item2['id'],
            'homeworkReviewId'=>$result1review2['id'],
            'score'=>6
        ));

        $result2=$this->getResultDao()->addResult(array('userId'=>$user->id,'homeworkId'=>$homework1['id'],'status'=>'finished'));
        $result2Item1=$this->getResultItemDao()->addItemResult(array(
            'itemId'=>1,
            'homeworkResultId'=>$result2['id'],
        ));
        $result2Item2=$this->getResultItemDao()->addItemResult(array(
            'itemId'=>2,
            'homeworkResultId'=>$result2['id'],
        ));
        $result2review1=$this->getReviewDao()->create(array(
            'homeworkResultId'=>$result2['id'],
            'category'=>'student',
            'homeworkId'=>$homework1['id'],
            'userId'=>$user->id
        ));
        $this->getReviewItemDao()->create(array(
            'homeworkResultId'=>$result2['id'],
            'homeworkItemResultId'=>$result2Item1['id'],
            'homeworkReviewId'=>$result2review1['id'],
            'score'=>6
        ));
        $this->getReviewItemDao()->create(array(
            'homeworkResultId'=>$result2['id'],
            'homeworkItemResultId'=>$result2Item2['id'],
            'homeworkReviewId'=>$result2review1['id'],
            'score'=>8
        ));

        $result3=$this->getResultDao()->addResult(array('userId'=>$user->id,'homeworkId'=>$homework1['id'],'status'=>'finished'));
        $result3review1=$this->getReviewDao()->create(array(
            'homeworkResultId'=>$result3['id'],
            'category'=>'student',
            'homeworkId'=>$homework1['id'],
            'userId'=>$user->id
        ));

        $result4=$this->getResultDao()->addResult(array('userId'=>$user->id,'homeworkId'=>$homework2['id'],'status'=>'pairReviewing'));
        $result4Item1=$this->getResultItemDao()->addItemResult(array(
            'itemId'=>1,
            'homeworkResultId'=>$result4['id'],
        ));
        $result4Item2=$this->getResultItemDao()->addItemResult(array(
            'itemId'=>2,
            'homeworkResultId'=>$result4['id'],
        ));
        $result4review1=$this->getReviewDao()->create(array(
            'homeworkResultId'=>$result4['id'],
            'homeworkId'=>$homework2['id'],
            'category'=>'student'
        ));
        $this->getReviewItemDao()->create(array(
            'homeworkResultId'=>$result4['id'],
            'homeworkItemResultId'=>$result4Item1['id'],
            'homeworkReviewId'=>$result4review1['id'],
            'score'=>2
        ));
        $this->getReviewItemDao()->create(array(
            'homeworkResultId'=>$result4['id'],
            'homeworkItemResultId'=>$result4Item2['id'],
            'homeworkReviewId'=>$result4review1['id'],
            'score'=>4
        ));

        $this->getHomeworkService()->forwardHomeworkStatus();
        $l1=$this->getResultDao()->getResult($result1['id']);
        $this->assertEquals('finished',$l1['status']);
        $this->assertEquals(13,$l1['score']);
        $l2=$this->getResultDao()->getResult($result2['id']);
        $this->assertEquals('finished',$l2['status']);
        $l3=$this->getResultDao()->getResult($result3['id']);
        $this->assertEquals('finished',$l3['status']);
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

    protected function getResultItemDao(){
        return $this->getServiceKernel()->createDao('Custom:Homework.ResultItemDao');
    }

    protected function getReviewDao(){
        return $this->getServiceKernel()->createDao('Custom:Homework.ReviewDao');
    }

    protected function getReviewItemDao(){
        return $this->getServiceKernel()->createDao('Custom:Homework.ReviewItemDao');
    }
}