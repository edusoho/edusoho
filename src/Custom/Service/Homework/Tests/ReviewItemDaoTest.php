<?php
namespace Custom\Service\Homework\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Announcement\AnnouncementService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\CurrentUser;
use Doctrine\DBAL\Query\QueryBuilder;

class ReviewItemDaoTest extends BaseTestCase
{
    public function testAverageItemScores(){

    	$review1 = $this->getReviewDao()->create(array(
    		'homeworkResultId'=> 1,
    		'category'=>'student'
    	));
    	$this->getReviewItemDao()->create(array(
    		'homeworkResultId'=>$review1['homeworkResultId'],
    		'homeworkItemResultId' =>1,
    		'homeworkReviewId'=>$review1['id'],
    		'score'=>10
    	));
    	$this->getReviewItemDao()->create(array(
    		'homeworkResultId'=>$review1['homeworkResultId'],
    		'homeworkItemResultId' =>2,
    		'homeworkReviewId'=>$review1['id'],
    		'score'=>6
    	));
    	$this->getReviewItemDao()->create(array(
    		'homeworkResultId'=>$review1['homeworkResultId'],
    		'homeworkItemResultId' =>3,
    		'homeworkReviewId'=>$review1['id'],
    		'score'=>8
    	));

    	$review2 = $this->getReviewDao()->create(array(
    		'homeworkResultId'=>1,
    		'category'=>'student'
    	));
    	$this->getReviewItemDao()->create(array(
    		'homeworkResultId'=>$review2['homeworkResultId'],
    		'homeworkItemResultId' =>1,
    		'homeworkReviewId'=>$review2['id'],
    		'score'=>6
    	));
    	$this->getReviewItemDao()->create(array(
    		'homeworkResultId'=>$review2['homeworkResultId'],
    		'homeworkItemResultId' =>2,
    		'homeworkReviewId'=>$review2['id'],
    		'score'=>2
    	));
    	$this->getReviewItemDao()->create(array(
    		'homeworkResultId'=>$review2['homeworkResultId'],
    		'homeworkItemResultId' =>3,
    		'homeworkReviewId'=>$review2['id'],
    		'score'=>4
    	));

    	$review3 = $this->getReviewDao()->create(array(
    		'homeworkResultId'=>1,
    		'category'=>'teacher'
    	));
    	$this->getReviewItemDao()->create(array(
    		'homeworkResultId'=>$review3['homeworkResultId'],
    		'homeworkItemResultId' =>1,
    		'homeworkReviewId'=>$review3['id'],
    		'score'=>4
    	));
    	$this->getReviewItemDao()->create(array(
    		'homeworkResultId'=>$review3['homeworkResultId'],
    		'homeworkItemResultId' =>2,
    		'homeworkReviewId'=>$review3['id'],
    		'score'=>0
    	));
    	$this->getReviewItemDao()->create(array(
    		'homeworkResultId'=>$review3['homeworkResultId'],
    		'homeworkItemResultId' =>3,
    		'homeworkReviewId'=>$review3['id'],
    		'score'=>4
    	));
        $averages=$this->getReviewItemDao()->averageItemScores($review1['homeworkResultId']);
        $this->assertEquals(3,count($averages));
        $this->assertEquals(8,$averages[0]['score']);
        $this->assertEquals(4,$averages[1]['score']);
        $this->assertEquals(6,$averages[2]['score']);
    }

    protected function getReviewItemDao(){
        return $this->getServiceKernel()->createDao('Custom:Homework.ReviewItemDao');
    }

    protected function getReviewDao(){
        return $this->getServiceKernel()->createDao('Custom:Homework.ReviewDao');
    }
}