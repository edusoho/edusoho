<?php
namespace Topxia\Service\Article\Tests;

use Topxia\Service\Common\BaseTestCase;

class ArticleServiceTest extends BaseTestCase
{
	public function testCreateArticle()
	{
		$fields = array('title' => 'test article', 'type' => 'article');

		$Article = $this->getArticleService()->createArticle($fields);

		$this->assertGreaterThan(0, $Article['id']);
		$this->assertEquals($fields['title'], $Article['title']);
		$this->assertEquals($fields['type'], $Article['type']);
	}

	public function testUpdateArticle()
	{
		$Article = $this->getArticleService()->createArticle(array(
			'title' => 'test article',
			'type' => 'article'
		));

		$fields = array(
			'title' => 'updated title',
			'body' => 'updated body',
		);
		$updated = $this->getArticleService()->updateArticle($Article['id'], $fields);

		$this->assertEquals($fields['title'], $updated['title']);
		$this->assertEquals($fields['body'], $updated['body']);
	}

    protected function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }
}