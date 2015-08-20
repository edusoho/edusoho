<?php
namespace Custom\Service\Homework\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Announcement\AnnouncementService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\CurrentUser;
use Doctrine\DBAL\Query\QueryBuilder;

class ResultDaoTest extends BaseTestCase
{
    // public function testFindSubmitableResultIds(){

    //     $homework1=$this->getHomeworkDao()->addHomework(array('completeTime'=>strtotime('-1 hours', time())));
    //     $homework2=$this->getHomeworkDao()->addHomework(array('completeTime'=>strtotime('+1 hours', time())));
    //     $result1=$this->getResultDao()->addResult(array('homeworkId'=>$homework1['id'],'status'=>'editing'));
    //     $result2=$this->getResultDao()->addResult(array('homeworkId'=>$homework1['id'],'status'=>'finished'));
    //     $result3=$this->getResultDao()->addResult(array('homeworkId'=>$homework2['id'],'status'=>'editing'));

    //     $results=$this->getResultDao()->findSubmitableResults();
    //     $ids=array();
    //     foreach($results as $result){
    //         array_push($ids,$result['id']);
    //     }
    //     $this->assertContains($result1['id'],$ids);
    //     $this->assertNotContains($result2['id'],$ids);
    //     $this->assertNotContains($result3['id'],$ids);
    // }

    public function testFindFinishableResults(){
        $homework1=$this->getHomeworkDao()->addHomework(array('reviewEndTime'=>strtotime('-1 hours', time())));
        $homework2=$this->getHomeworkDao()->addHomework(array('reviewEndTime'=>strtotime('+1 hours', time())));
        $result1=$this->getResultDao()->addResult(array('homeworkId'=>$homework1['id'],'status'=>'pairReviewing'));
        $result2=$this->getResultDao()->addResult(array('homeworkId'=>$homework1['id'],'status'=>'finished'));
        $result3=$this->getResultDao()->addResult(array('homeworkId'=>$homework2['id'],'status'=>'editing'));

        $results=$this->getResultDao()->findFinishableResults();
        $ids=array();
        foreach($results as $result){
            array_push($ids,$result['id']);
        }
        $this->assertContains($result1['id'],$ids);
        $this->assertNotContains($result2['id'],$ids);
        $this->assertNotContains($result3['id'],$ids);
    }

    protected function getResultDao(){
        return $this->getServiceKernel()->createDao('Custom:Homework.ResultDao');
    }

    protected function getHomeworkDao(){
        return $this->getServiceKernel()->createDao('Homework:Homework.HomeworkDao');
    }
}