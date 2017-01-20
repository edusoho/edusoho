<?php
namespace Tests\Content;

use Biz\BaseTestCase;
use Biz\Content\Service\CommentService;

class CommentServiceTest extends BaseTestCase
{

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
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
     * @return CommentService
     */
	protected function getCommentService()
    {
        return $this->getBiz()->service('Content:CommentService');
    }

}