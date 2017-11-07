<?php

namespace Tests\Unit\Article;

use Biz\Article\Service\ArticleService;
use Biz\Article\Service\CategoryService;
use Biz\System\Service\SettingService;
use Biz\Taxonomy\Service\TagService;
use Biz\User\Service\UserService;
use Biz\User\CurrentUser;
use Biz\BaseTestCase;

class ArticleServiceTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->getSettingService()->set('site', array('name' => 'name', 'slogan' => 'slogan'));
        $this->getSettingService()->set('mobile', array('about' => 'about', 'logo' => 'logo'));
    }

    public function testGetArticle()
    {
        $newArticle = $this->createArticle();
        $getArticle = $this->getArticleService()->getArticle($newArticle['id']);
        $this->assertEquals('test article', $getArticle['title']);
        $this->assertEquals('正午时分', $getArticle['body']);
    }

    public function testGetArticlePrevious()
    {
        $newArticle = $this->createArticle();
        $this->createArticleSecond();
        $this->getArticleService()->getArticlePrevious($newArticle['id']);
    }

    public function testGetArticleNext()
    {
        $newArticle = $this->createArticle();
        sleep(1);
        $fields = array(
            'publishedTime' => 'now',
            'title' => 'test article2',
            'type' => 'article',
            'body' => '正午时分',
            'thumb' => 'thumb',
            'originalThumb' => 'originalThumb',
            'categoryId' => $newArticle['categoryId'],
            'source' => 'http://www.edusoho.com',
            'sourceUrl' => 'http://www.edusoho.com',
            'tags' => 'default',
        );
        $newArticle2 = $this->getArticleService()->createArticle($fields);
        $getArticle = $this->getArticleService()->getArticleNext($newArticle['id']);

        $this->assertEquals($newArticle2['id'], $getArticle['id']);
    }

    public function testDeleteArticlesByIds()
    {
        $newArticle = $this->createArticle();
        $newArticle2 = $this->createArticle();
        $this->getArticleService()->deleteArticlesByIds(array($newArticle['id'], $newArticle2['id']));

        $this->assertEquals(null, $this->getArticleService()->getArticle($newArticle['id']));
        $this->assertEquals(null, $this->getArticleService()->getArticle($newArticle2['id']));
    }

    public function testFindAllArticles()
    {
        $this->createArticle();
        $this->createArticleSecond();

        $article = $this->getArticleService()->findAllArticles();
        $this->assertEquals('2', count($article));
    }

    public function testFindArticlesByIds()
    {
        $newarticle1 = $this->createArticle();
        $newarticle2 = $this->createArticle();
        $ids = array(
            $newarticle1['id'],
            $newarticle2['id'],
        );
        $findArticles = $this->getArticleService()->findArticlesByIds($ids);
        $this->assertEquals('2', count($findArticles));
    }

    public function testSearchArticles()
    {
        $this->createArticle();
        $this->createArticleSecond();
        $conditions = array(
            'status' => 'published',
        );
        $result = $this->getArticleService()->searchArticles($conditions, 'published', 0, 20);
        $this->assertEquals('2', count($result));
    }

    public function testCountArticles()
    {
        $this->createArticle();
        $this->createArticleSecond();
        $conditions = array(
            'status' => 'published',
        );
        $result = $this->getArticleService()->countArticles($conditions);
        $this->assertEquals('2', $result);
    }

    public function testCreateArticle()
    {
        $fields = array(
            'publishedTime' => 'now',
            'title' => 'test article',
            'type' => 'article',
            'body' => '正午时分',
            'thumb' => 'thumb',
            'originalThumb' => 'originalThumb',
            'categoryId' => '1',
            'source' => 'http://www.edusoho.com',
            'sourceUrl' => 'http://www.edusoho.com',
            'tags' => '',
        );
        $article = $this->getArticleService()->createArticle($fields);
        $this->assertEquals('test article', $article['title']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testcreateEmptyArticle()
    {
        $article = null;
        $this->getArticleService()->createArticle($article);
    }

    public function testupdateArticle()
    {
        $newArticle = $this->createArticle();
        $fields = array(
            'publishedTime' => 'now',
            'title' => 'test article2',
            'type' => 'article2',
            'body' => '正午时分2',
            'thumb' => 'thumb',
            'originalThumb' => 'originalThumb',
            'categoryId' => '1',
            'source' => 'http://www.edusoho.com',
            'sourceUrl' => 'http://www.edusoho.com',
            'tags' => '1,2',
        );
        $article = $this->getArticleService()->updateArticle($newArticle['id'], $fields);
        $this->assertEquals('正午时分2', $article['body']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testupdateEmptyArticle()
    {
        $article = null;
        $this->getArticleService()->updateArticle($article['id'], $article);
    }

    public function testhitArticle()
    {
        $newArticle = $this->createArticle();
        $num = 5;

        while ($num <= 10) {
            $this->getArticleService()->hitArticle($newArticle['id']);
            $num = $num + 1;
        }

        $getArticle = $this->getArticleService()->getArticle($newArticle['id']);
        $this->assertEquals('6', $getArticle['hits']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testhitEmptyArticle()
    {
        $article = null;
        $this->getArticleService()->hitArticle($article['id']);
    }

    public function testGetArticleLike()
    {
        $user = $this->getCurrentUser();

        $newArticle = $this->createArticle();
        $this->getArticleService()->like($newArticle['id']);

        $like = $this->getArticleService()->getArticleLike($newArticle['id'], $user['id']);

        $this->assertNotNull($like);
    }

    public function testLike()
    {
        $user = $this->getCurrentUser();

        $newArticle = $this->createArticle();
        $this->getArticleService()->like($newArticle['id']);

        $like = $this->getArticleService()->getArticleLike($newArticle['id'], $user['id']);
        $this->assertNotNull($like);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testlikeWithEmptyContent()
    {
        $newArticle = null;
        $this->getArticleService()->like($newArticle['id']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testlikeTwice()
    {
        $newArticle = $this->createArticle();
        $this->getArticleService()->like($newArticle['id']);
        $this->getArticleService()->like($newArticle['id']);
    }

    public function testcancelLike()
    {
        $currentUser = $this->getCurrentUser();

        $newArticle = $this->createArticle();
        $this->getArticleService()->like($newArticle['id']);

        $like = $this->getArticleService()->getArticleLike($newArticle['id'], $currentUser['id']);
        $this->assertNotNull($like);

        $this->getArticleService()->cancellike($newArticle['id']);
        $like = $this->getArticleService()->getArticleLike($newArticle['id'], $currentUser['id']);
        $this->assertNull($like);
    }

    public function testsetArticleProperty()
    {
        $property = 'promoted';
        $newArticle = $this->createArticle();
        $this->getArticleService()->setArticleProperty($newArticle['id'], $property);
        $getArticle = $this->getArticleService()->getArticle($newArticle['id']);
        $this->assertEquals('1', $getArticle['promoted']);
    }

    public function testcancelArticleProperty()
    {
        $property = 'promoted';
        $newArticle = $this->createArticle();
        $this->getArticleService()->setArticleProperty($newArticle['id'], $property);
        $getArticle = $this->getArticleService()->getArticle($newArticle['id']);
        $this->assertEquals('1', $getArticle['promoted']);
        $this->getArticleService()->cancelArticleProperty($newArticle['id'], $property);
        $getArticle = $this->getArticleService()->getArticle($newArticle['id']);
        $this->assertEquals('0', $getArticle['promoted']);
    }

    public function testtrashArticle()
    {
        $newArticle = $this->createArticle();
        $this->getArticleService()->trashArticle($newArticle['id']);
        $trashArticle = $this->getArticleService()->getArticle($newArticle['id']);
        $this->assertEquals('trash', $trashArticle['status']);
    }

    public function testpublishArticle()
    {
        $newArticle = $this->createArticle();
        $this->getArticleService()->publishArticle($newArticle['id']);
        $getArticle = $this->getArticleService()->getArticle($newArticle['id']);
        $this->assertGreaterThan(0, $getArticle['publishedTime']);
        $this->assertEquals($getArticle['status'], 'published');
    }

    public function testunpublishArticle()
    {
        $newArticle = $this->createArticle();
        $this->getArticleService()->unpublishArticle($newArticle['id']);
        $getArticle = $this->getArticleService()->getArticle($newArticle['id']);
        $this->assertEquals($getArticle['status'], 'unpublished');
    }

    public function testFindRelativeArticles()
    {
        $tag1 = $this->getTagService()->addTag(array('name' => 'tag1'));
        $tag2 = $this->getTagService()->addTag(array('name' => 'tag2'));
        $tag3 = $this->getTagService()->addTag(array('name' => 'tag3'));
        $article1 = array(
            'publishedTime' => 'now',
            'title' => 'test article1',
            'type' => 'article',
            'body' => '正午时分',
            'thumb' => 'thumb',
            'originalThumb' => 'originalThumb',
            'categoryId' => '1',
            'source' => 'http://www.edusoho.com',
            'sourceUrl' => 'http://www.edusoho.com',
            'tags' => sprintf('%s,%s', $tag1['name'], $tag2['name']),
        );
        $article1 = $this->getArticleService()->createArticle($article1);

        $article2 = array(
            'publishedTime' => 'now',
            'title' => 'test article2',
            'type' => 'article',
            'body' => '正午时分',
            'thumb' => 'thumb',
            'originalThumb' => 'originalThumb',
            'categoryId' => '1',
            'source' => 'http://www.edusoho.com',
            'sourceUrl' => 'http://www.edusoho.com',
            'tags' => sprintf('%s,%s,%s', $tag1['name'], $tag2['name'], $tag3['name']),
        );
        $this->getArticleService()->createArticle($article2);

        $article3 = array(
            'publishedTime' => 'now',
            'title' => 'test article3',
            'type' => 'article',
            'body' => '正午时分',
            'thumb' => 'thumb',
            'originalThumb' => 'originalThumb',
            'categoryId' => '1',
            'source' => 'http://www.edusoho.com',
            'sourceUrl' => 'http://www.edusoho.com',
            'tags' => sprintf('%s', $tag3['name']),
        );
        $article3 = $this->getArticleService()->createArticle($article3);

        $relativeArticles = $this->getArticleService()->findRelativeArticles($article1['id'], 3);
        $this->assertEquals(1, count($relativeArticles));

        $relativeArticles = $this->getArticleService()->findRelativeArticles($article3['id'], 3);
        $this->assertEquals(1, count($relativeArticles));
    }

    protected function createArticle()
    {
        $category = $this->getCategory();

        $fields = array(
            'publishedTime' => 'now',
            'title' => 'test article',
            'type' => 'article',
            'body' => '正午时分',
            'thumb' => 'thumb',
            'originalThumb' => 'originalThumb',
            'categoryId' => $category['id'],
            'source' => 'http://www.edusoho.com',
            'sourceUrl' => 'http://www.edusoho.com',
            'tags' => 'default',
        );

        return $this->getArticleService()->createArticle($fields);
    }

    protected function getCategory()
    {
        $category = $this->getCategoryService()->getCategoryByCode('article');

        if (!$category) {
            $category = array(
                'name' => '文章',
                'code' => 'article',
                'parentId' => 0,
            );

            return $this->getCategoryService()->createCategory($category);
        }

        return $category;
    }

    protected function createArticleSecond()
    {
        $category = $this->getCategory();

        $fields = array(
            'publishedTime' => 'now',
            'title' => 'test article2',
            'type' => 'article2',
            'body' => '正午时分2',
            'thumb' => 'thumb2',
            'originalThumb' => 'originalThumb2',
            'categoryId' => $category['id'],
            'source' => 'http://try6.edusoho.cn',
            'sourceUrl' => 'http://try6.edusoho.cn',
            'tags' => 'default',
        );

        return $this->getArticleService()->createArticle($fields);
    }

    protected function createUser($user)
    {
        $userInfo = array();
        $userInfo['email'] = "{$user}@{$user}.com";
        $userInfo['nickname'] = "{$user}";
        $userInfo['password'] = "{$user}";
        $userInfo['loginIp'] = '127.0.0.1';

        return $this->getUserService()->register($userInfo);
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return ArticleService
     */
    protected function getArticleService()
    {
        return $this->createService('Article:ArticleService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Article:CategoryService');
    }
}
