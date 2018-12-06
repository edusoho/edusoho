<?php

namespace Tests\Unit\Content\Service;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\Content\Service\CommentService;
use AppBundle\Common\TimeMachine;

class CommentServiceTest extends BaseTestCase
{
    /**
     * @expectedException \Biz\Content\CommentException
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

    /**
     * @expectedException \Biz\Course\CourseException
     */
    public function testCreateCommentNoFindCourse()
    {
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array(),
                    'withParams' => array(111),
                ),
            )
        );
        $this->getCommentService()->createComment(array('objectType' => 'course', 'objectId' => 111));
    }

    public function testCreateComment()
    {
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array('id' => 111),
                    'withParams' => array(111),
                ),
            )
        );

        TimeMachine::setMockedTime(1515586775);
        $this->mockBiz(
            'Content:CommentDao',
            array(
                array(
                    'functionName' => 'create',
                    'returnValue' => array('id' => 111, 'objectType' => 'course'),
                    'withParams' => array(array(
                        'objectType' => 'course',
                        'objectId' => 111,
                        'content' => 'content',
                        'userId' => $this->getCurrentUser()->id,
                        'createdTime' => TimeMachine::time(),
                    )),
                ),
            )
        );
        $result = $this->getCommentService()->createComment(array('objectType' => 'course', 'objectId' => 111, 'content' => 'content'));
        $this->assertEquals(array('id' => 111, 'objectType' => 'course'), $result);
    }

    public function testGetComment()
    {
        $this->mockBiz(
            'Content:CommentDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'objectType' => 'course'),
                    'withParams' => array(111),
                ),
            )
        );
        $result = $this->getCommentService()->getComment(111);

        $this->assertEquals(array('id' => 111, 'objectType' => 'course'), $result);
    }

    public function testFindComments()
    {
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array('id' => 111),
                    'withParams' => array(111),
                ),
            )
        );
        $this->mockBiz(
            'Content:CommentDao',
            array(
                array(
                    'functionName' => 'findByObjectTypeAndObjectId',
                    'returnValue' => array(array('id' => 111, 'objectType' => 'course')),
                    'withParams' => array('course', 111, 0, 5),
                ),
            )
        );
        $result = $this->getCommentService()->findComments('course', 111, 0, 5);
        $this->assertEquals(array(array('id' => 111, 'objectType' => 'course')), $result);
    }

    public function testDeleteComment()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin3',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_SUPER_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'Content:CommentDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'objectType' => 'course', 'userId' => $this->getCurrentUser()->id),
                    'withParams' => array(111),
                ),
                array(
                    'functionName' => 'delete',
                    'returnValue' => 1,
                    'withParams' => array(111),
                ),
            )
        );
        $result = $this->getCommentService()->deleteComment(111);

        $this->assertEquals(1, $result);
    }

    /**
     * @expectedException \Biz\Content\CommentException
     */
    public function testDeleteCommentWithEmptyComment()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin3',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_SUPER_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'Content:CommentDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(),
                    'withParams' => array(111),
                ),
            )
        );
        $this->getCommentService()->deleteComment(111);
    }

    /**
     * @expectedException \Biz\User\UserException
     */
    public function testDeleteCommentWithEmptyUser()
    {
        $this->getServiceKernel()->setCurrentUser(array());
        $this->mockBiz(
            'Content:CommentDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'objectType' => 'course'),
                    'withParams' => array(111),
                ),
            )
        );
        $this->getCommentService()->deleteComment(111);
    }

    /**
     * @expectedException \Biz\Content\CommentException
     */
    public function testDeleteCommentWithNotAdminUser()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin3',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'Content:CommentDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'objectType' => 'course', 'userId' => 111),
                    'withParams' => array(111),
                ),
            )
        );
        $this->getCommentService()->deleteComment(111);
    }

    public function testGetCommentsByType()
    {
        $this->mockBiz(
            'Content:CommentDao',
            array(
                array(
                    'functionName' => 'findByObjectType',
                    'returnValue' => array(array('id' => 111, 'objectType' => 'course', 'userId' => 111)),
                    'withParams' => array('course', 0, 5),
                ),
            )
        );
        $result = $this->getCommentService()->getCommentsByType('course', 0, 5);

        $this->assertEquals(array(array('id' => 111, 'objectType' => 'course', 'userId' => 111)), $result);
    }

    public function testGetCommentsCountByType()
    {
        $this->mockBiz(
            'Content:CommentDao',
            array(
                array(
                    'functionName' => 'countByObjectType',
                    'returnValue' => 1,
                    'withParams' => array('course'),
                ),
            )
        );
        $result = $this->getCommentService()->getCommentsCountByType('course');

        $this->assertEquals(1, $result);
    }

    /**
     * @return CommentService
     */
    protected function getCommentService()
    {
        return $this->createService('Content:CommentService');
    }
}
