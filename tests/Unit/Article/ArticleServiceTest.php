<?php

namespace Tests\Unit\Article;

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

    public function testFindArticlesByCategoryIds()
    {
        $this->mockBiz(
            'Article:ArticleDao',
            array(
                array(
                    'functionName' => 'searchByCategoryIds',
                    'returnValue' => array(array('id' => 111, 'title' => 'title')),
                    'withParams' => array(
                        array(111, 222),
                        0,
                        5,
                    ),
                ),
            )
        );
        $result = $this->getArticleService()->findArticlesByCategoryIds(array(111, 222), 0, 5);

        $this->assertEquals(array(array('id' => 111, 'title' => 'title')), $result);
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

    public function testFindArticlesCount()
    {
        $this->mockBiz(
            'Article:ArticleDao',
            array(
                array(
                    'functionName' => 'countByCategoryIds',
                    'returnValue' => 2,
                    'withParams' => array(
                        array(1, 2),
                    ),
                ),
            )
        );
        $count = $this->getArticleService()->findArticlesCount(array(1, 2));

        $this->assertEquals(2, $count);
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

    public function testBatchUpdateOrg()
    {
        $this->mockBiz(
            'Article:ArticleDao',
            array(
                array(
                    'functionName' => 'update',
                    'returnValue' => 1,
                    'withParams' => array(
                        1,
                        array(),
                    ),
                ),
            )
        );
        $result = $this->getArticleService()->batchUpdateOrg(1, null);

        $this->getArticleDao()->shouldHaveReceived('update');
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

    public function testCount()
    {
        $this->mockBiz(
            'Article:ArticleDao',
            array(
                array(
                    'functionName' => 'waveArticle',
                    'returnValue' => array('id' => 1, 'hits' => 3),
                    'withParams' => array(1, 'hits', 2),
                ),
            )
        );
        $result = $this->getArticleService()->count(1, 'hits', 2);

        $this->getArticleDao()->shouldHaveReceived('waveArticle');
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

    public function testRemoveArticlethumb()
    {
        $newArticle = $this->createArticle();
        $this->mockBiz(
            'Content:FileService',
            array(
                array(
                    'functionName' => 'deleteFileByUri',
                    'withParams' => array('thumb'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'deleteFileByUri',
                    'withParams' => array('originalThumb'),
                    'runTimes' => 2,
                ),
            )
        );
        $this->mockBiz(
            'System:LogService',
            array(
                array(
                    'functionName' => 'info',
                    'withParams' => array('article', 'removeThumb', '文章#1removeThumb'),
                ),
            )
        );
        $result = $this->getArticleService()->removeArticlethumb(1);

        $this->getFileService()->shouldHaveReceived('deleteFileByUri', array('thumb'));
        $this->getFileService()->shouldHaveReceived('deleteFileByUri', array('originalThumb'));
        $this->getLogService()->shouldHaveReceived('info');
    }

    public function testDeleteArticle()
    {
        $newArticle = $this->createArticle();
        $this->mockBiz(
            'System:LogService',
            array(
                array(
                    'functionName' => 'info',
                    'withParams' => array('article', 'delete', '文章#1永久删除'),
                ),
            )
        );
        $result = $this->getArticleService()->deleteArticle(1);

        $this->getLogService()->shouldHaveReceived('info');

        $this->assertTrue($result);
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

    public function testChangeIndexPicture()
    {
        $this->mockBiz(
            'Content:FileService',
            array(
                array(
                    'functionName' => 'getFilesByIds',
                    'withParams' => array(array(1)),
                    'returnValue' => array(array('id' => 1, 'uri' => 'test')),
                ),
                array(
                    'functionName' => 'getFileObject',
                    'withParams' => array(1),
                    'returnValue' => array(array('id' => 1, 'uri' => 'test')),
                ),
                array(
                    'functionName' => 'uploadFile',
                    'withParams' => array('article', array(array('id' => 1, 'uri' => 'test'))),
                    'returnValue' => array(array('id' => 1, 'uri' => 'test')),
                ),
                array(
                    'functionName' => 'deleteFileByUri',
                    'withParams' => array('test'),
                ),
            )
        );
        $result = $this->getArticleService()->changeIndexPicture(array(array('id' => 1, 'type' => 'origin')));

        $this->getFileService()->shouldHaveReceived('deleteFileByUri');
        $this->assertArrayHasKey('file', $result['origin']);

        $result = $this->getArticleService()->changeIndexPicture(array(array('id' => 1, 'type' => 'new')));
        $this->assertArrayHasKey('file', $result['new']);
    }

    public function testFindPublishedArticlesByTagIdsAndCount()
    {
        $this->mockBiz(
            'Taxonomy:TagService',
            array(
                array(
                    'functionName' => 'findTagOwnerRelationsByTagIdsAndOwnerType',
                    'withParams' => array(array(1), 'article'),
                    'returnValue' => array(array('id' => 1, 'title' => 'title')),
                ),
            )
        );
        $this->mockBiz(
            'Article:ArticleDao',
            array(
                array(
                    'functionName' => 'search',
                    'withParams' => array(
                        array('articleIds' => array(1), 'status' => 'published'),
                        array('publishedTime' => 'DESC'),
                        0,
                        5,
                    ),
                    'returnValue' => array(array('id' => 1, 'title' => 'test')),
                ),
            )
        );
        $result = $this->getArticleService()->findPublishedArticlesByTagIdsAndCount(array(1), 5);

        $this->assertEquals(array(array('id' => 1, 'title' => 'test')), $result);
    }

    public function testViewArticle()
    {
        $result1 = $this->getArticleService()->viewArticle(1);

        $this->assertEquals(array(), $result1);

        $newArticle = $this->createArticle();
        $result2 = $this->getArticleService()->viewArticle(1);

        $this->assertEquals($newArticle, $result2);
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

    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return ArticleDao
     */
    protected function getArticleDao()
    {
        return $this->createDao('Article:ArticleDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
