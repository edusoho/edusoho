<?php
namespace Topxia\Service\Content\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Content\BlockService;
use Topxia\Common\ArrayToolkit;

class CommentServiceTest extends BaseTestCase
{   

	/**
     * @expectedException Topxia\Service\Common\ServiceException
     */
	public function testCreateCommentNoObjectType()
	{
		$comment = array(
			'objectType' => '',
			'objectId' => '',
			'content' => '',
		);

		$this->getCommentService()->createComment($comment);
	}

	//发现会向http://estui.edusoho.net/v1/tui/student/add发送数据.暂时取消
	// public function testCreateComment()
	// {
	// 	$createCourse = $this->getCourseService()->createCourse(array(
 //            'title' => 'online test course 1'
 //        ));

	// 	$comment = array(
	// 		'objectType' => 'course',
	// 		'objectId' => $createCourse['id'],
	// 		'content' => 'test one',
	// 	);

	// 	$this->getCommentService()->createComment($comment);
	// }

	protected function getCommentService()
    {
        return $this->getServiceKernel()->createService('Content.CommentService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}