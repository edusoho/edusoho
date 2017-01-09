<?php
namespace Tests\Article;

use Biz\Article\Service\ArticleService;
use Biz\Article\Service\CategoryService;
use Biz\System\Service\SettingService;
use Biz\Taxonomy\Service\TagService;
use Biz\User\Service\UserService;
use Biz\User\CurrentUser;
use Biz\BaseTestCase;;


class ArticleServiceTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->getSettingService()->set('site', array('name' => 'name', 'slogan' => 'slogan'));
        $this->getSettingService()->set('mobile', array('about' => 'about', 'logo' => 'logo'));
    }

    public function testgetArticle()
    {
        $user       = $this->getCurrentUser();
        $newArticle = $this->createArticle();
        $getArticle = $this->getArticleService()->getArticle($newArticle['id']);
        $this->assertEquals('test article', $getArticle['title']);
        $this->assertEquals('正午时分', $getArticle['body']);
    }

    public function testgetArticlePrevious()
    {
        $newArticle     = $this->createArticle();
        $newArticlesend = $this->createArticlesencond();
        $getArticle     = $this->getArticleService()->getArticlePrevious($newArticle['id']);
    }

    public function testgetArticleNext()
    {
        $newArticle = $this->createArticle();
        sleep(2);

        $fields = array(
            'publishedTime' => 'now',
            'title'         => 'test article2',
            'type'          => 'article',
            'body'          => '正午时分',
            'thumb'         => 'thumb',
            'originalThumb' => 'originalThumb',
            'categoryId'    => $newArticle['categoryId'],
            'source'        => 'http://www.edusoho.com',
            'sourceUrl'     => 'http://www.edusoho.com',
            'tags'          => 'default',
        );
        $newArticlesend = $this->getArticleService()->createArticle($fields);
        $getArticle     = $this->getArticleService()->getArticleNext($newArticle['id']);

        $this->assertEquals($newArticlesend['id'], $getArticle['id']);
    }

    public function testDeleteArticlesByIds()
    {
        $newArticle     = $this->createArticle();
        $newArticlesend = $this->createArticle();
        $this->getArticleService()->deleteArticlesByIds(array($newArticle['id'], $newArticlesend['id']));

        $this->assertEquals(null, $this->getArticleService()->getArticle($newArticle['id']));
        $this->assertEquals(null, $this->getArticleService()->getArticle($newArticlesend['id']));
    }

    public function testfindAllArticles()
    {
        $newarticle  = $this->createArticle();
        $newarticle2 = $this->createArticlesencond();

        $article = $this->getArticleService()->findAllArticles();
        $this->assertEquals('2', count($article));
    }

    public function testfindArticlesByIds()
    {
        $newarticle1 = $this->createArticle();
        $newarticle2 = $this->createArticle();
        $ids         = array(
            $newarticle1['id'],
            $newarticle2['id']
        );
        $findArticles = $this->getArticleService()->findArticlesByIds($ids);
        $this->assertEquals('2', count($findArticles));
    }

    public function testsearchArticles()
    {
        $newArticle  = $this->createArticle();
        $newarticle2 = $this->createArticlesencond();
        $conditions  = array(
            'status' => 'published'
        );
        $result = $this->getArticleService()->searchArticles($conditions, 'published', 0, 20);
        $this->assertEquals('2', count($result));
    }

    public function testsearchArticlesCount()
    {
        $newArticle  = $this->createArticle();
        $newarticle2 = $this->createArticlesencond();
        $conditions  = array(
            'status' => 'published'
        );
        $result = $this->getArticleService()->searchArticlesCount($conditions, 'published', 0, 20);
        $this->assertEquals('2', $result);
    }

    public function testcreateArticle()
    {
        $fileds = array(
            'publishedTime' => 'now',
            'title'         => 'test article',
            'type'          => 'article',
            'body'          => '正午时分',
            'thumb'         => 'thumb',
            'originalThumb' => 'originalThumb',
            'categoryId'    => '1',
            'source'        => 'http://www.edusoho.com',
            'sourceUrl'     => 'http://www.edusoho.com',
            'tags'          => ''
        );
        $article = $this->getArticleService()->createArticle($fileds);
        $this->assertEquals('test article', $article['title']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testcreateEmptyArticle()
    {
        $article = null;
        $article = $this->getArticleService()->createArticle($article);
    }

    public function testupdateArticle()
    {
        $newArticle = $this->createArticle();
        $fields     = array(
            'publishedTime' => 'now',
            'title'         => 'test article2',
            'type'          => 'article2',
            'body'          => '正午时分2',
            'thumb'         => 'thumb',
            'originalThumb' => 'originalThumb',
            'categoryId'    => '1',
            'source'        => 'http://www.edusoho.com',
            'sourceUrl'     => 'http://www.edusoho.com',
            'tags'          => '1,2'
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
        $article = $this->getArticleService()->updateArticle($article['id'], $article);
    }

    public function testhitArticle()
    {
        $newArticle = $this->createArticle();
        $num        = 5;

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
        $article = $this->getArticleService()->hitArticle($article['id']);
    }

    public function testgetArticleLike()
    {
        $user        = $this->createCurrentUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $newArticle = $this->createArticle();
        $this->getArticleService()->like($newArticle['id']);

        $like = $this->getArticleService()->getArticleLike($newArticle['id'], $currentUser['id']);
        $this->assertNotNull($like);
    }

    public function testlike()
    {
        $user        = $this->createCurrentUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $newArticle = $this->createArticle();
        $this->getArticleService()->like($newArticle['id']);

        $like = $this->getArticleService()->getArticleLike($newArticle['id'], $currentUser['id']);
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
    public function testlikeTwich()
    {
        $newArticle = $this->createArticle();
        $this->getArticleService()->like($newArticle['id']);
        $this->getArticleService()->like($newArticle['id']);
    }

    public function testcancelLike()
    {
        $user        = $this->createCurrentUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

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
        $property   = 'promoted';
        $newArticle = $this->createArticle();
        $this->getArticleService()->setArticleProperty($newArticle['id'], $property);
        $getArticle = $this->getArticleService()->getArticle($newArticle['id']);
        $this->assertEquals('1', $getArticle['promoted']);
    }

    public function testcancelArticleProperty()
    {
        $property   = 'promoted';
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
        $tag1     = $this->getTagService()->addTag(array('name' => 'tag1'));
        $tag2     = $this->getTagService()->addTag(array('name' => 'tag2'));
        $tag3     = $this->getTagService()->addTag(array('name' => 'tag3'));
        $article1 = array(
            'publishedTime' => 'now',
            'title'         => 'test article1',
            'type'          => 'article',
            'body'          => '正午时分',
            'thumb'         => 'thumb',
            'originalThumb' => 'originalThumb',
            'categoryId'    => '1',
            'source'        => 'http://www.edusoho.com',
            'sourceUrl'     => 'http://www.edusoho.com',
            'tags'          => sprintf('%s,%s', $tag1['name'], $tag2['name'])
        );
        $article1 = $this->getArticleService()->createArticle($article1);

        $article2 = array(
            'publishedTime' => 'now',
            'title'         => 'test article2',
            'type'          => 'article',
            'body'          => '正午时分',
            'thumb'         => 'thumb',
            'originalThumb' => 'originalThumb',
            'categoryId'    => '1',
            'source'        => 'http://www.edusoho.com',
            'sourceUrl'     => 'http://www.edusoho.com',
            'tags'          => sprintf('%s,%s,%s', $tag1['name'], $tag2['name'], $tag3['name'])
        );
        $article2 = $this->getArticleService()->createArticle($article2);

        $article3 = array(
            'publishedTime' => 'now',
            'title'         => 'test article3',
            'type'          => 'article',
            'body'          => '正午时分',
            'thumb'         => 'thumb',
            'originalThumb' => 'originalThumb',
            'categoryId'    => '1',
            'source'        => 'http://www.edusoho.com',
            'sourceUrl'     => 'http://www.edusoho.com',
            'tags'          => sprintf('%s', $tag3['name'])
        );
        $article3 = $this->getArticleService()->createArticle($article3);

        $relativeArticles = $this->getArticleService()->findRelativeArticles($article1['id'], 3);
        $this->assertEquals(1, count($relativeArticles));

        $relativeArticles = $this->getArticleService()->findRelativeArticles($article3['id'], 3);
        $this->assertEquals(1, count($relativeArticles));
    }

    protected function createArticle()
    {
        $category = $this->createCategory();

        $fields = array(
            'publishedTime' => 'now',
            'title'         => 'test article',
            'type'          => 'article',
            'body'          => '正午时分',
            'thumb'         => 'thumb',
            'originalThumb' => 'originalThumb',
            'categoryId'    => $category['id'],
            'source'        => 'http://www.edusoho.com',
            'sourceUrl'     => 'http://www.edusoho.com',
            'tags'          => 'default',
        );
        return $this->getArticleService()->createArticle($fields);
    }

    protected function createCategory()
    {
        $category = array(
            'name'     => 'test article ' . random_int(0, 10000),
            'code'     => 'ta' . random_int(0, 10000),
            'parentId' => 0
        );
        return $this->getCategoryService()->createCategory($category);
    }

    protected function createArticlesencond()
    {
        $category = $this->createCategory();

        $fields = array(
            'publishedTime' => 'now',
            'title'         => 'test article2',
            'type'          => 'article2',
            'body'          => '正午时分2',
            'thumb'         => 'thumb2',
            'originalThumb' => 'originalThumb2',
            'categoryId'    => $category['id'],
            'source'        => 'http://try6.edusoho.cn',
            'sourceUrl'     => 'http://try6.edusoho.cn',
            'tags'          => 'default'
        );

        return $this->getArticleService()->createArticle($fields);
    }

    protected function createUser($user)
    {
        $userInfo             = array();
        $userInfo['email']    = "{$user}@{$user}.com";
        $userInfo['nickname'] = "{$user}";
        $userInfo['password'] = "{$user}";
        $userInfo['loginIp']  = '127.0.0.1';
        return $this->getUserService()->register($userInfo);
    }

    private function createCurrentUser()
    {
        $user              = array();
        $user['email']     = "user@user.com";
        $user['nickname']  = "user";
        $user['password']  = "user";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');
        return $user;
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->getBiz()->service('Taxonomy:TagService');
    }

    /**
     * @return ArticleService
     */
    protected function getArticleService()
    {
        return $this->getBiz()->service('Article:ArticleService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->getBiz()->service('Article:CategoryService');
    }

}
