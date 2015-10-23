<?php
namespace Topxia\Service\Content\Tests;

use Topxia\Service\Common\BaseTestCase;

class ContentServiceTest extends BaseTestCase
{
	public function testCreateContent()
	{
		$fields = array('title' => 'test article', 'type' => 'article');

		$content = $this->getContentService()->createContent($fields);

		$this->assertGreaterThan(0, $content['id']);
		$this->assertEquals($fields['title'], $content['title']);
		$this->assertEquals($fields['type'], $content['type']);
	}

	public function testUpdateContent()
	{
		$content = $this->getContentService()->createContent(array(
			'title' => 'test article',
			'type' => 'article'
		));

		$fields = array(
			'title' => 'updated title',
			'body' => 'updated body',
		);
		$updated = $this->getContentService()->updateContent($content['id'], $fields);

		$this->assertEquals($fields['title'], $updated['title']);
		$this->assertEquals($fields['body'], $updated['body']);
	}

    protected function getContentService()
    {
        return $this->getServiceKernel()->createService('Content.ContentService');
    }
}