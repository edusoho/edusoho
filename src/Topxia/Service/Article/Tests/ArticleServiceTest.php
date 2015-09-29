<?php
namespace Topxia\Service\Article\Impl;
use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\User\CurrentUser;
// use Topxia\Service\Common\BaseService;
// use Topxia\Service\Article\ArticleService;
// use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceException;

class ArticleServiceTest extends BaseTestCase
{
	public function testgetArticle()
	{
		$newArticle = $this->createArticle();
		$getArticle = $this->getArticleService()->getArticle($newArticle['id']);
		$this->assertEquals('test article',$getArticle['title']);
		$this->assertEquals('正午时分',$getArticle['body']);
	}

	// public function testgetArticlePrevious()
	// {
	// 	// $newArticle = $this->createArticle();
	// 	// $newArticlesend = $this->createArticlesencond();
	// 	// $getArticle = $this->getArticleService()->getArticlePrevious($newArticle['id']);
	// 	// var_dump($getArticle);exit();
	// 	// 待定
	// }

	// public function testgetArticleByAlias()
	// {
	// 	// article表里并没有alias字段
	// }

	public function testfindAllArticles()
	{
		$newarticle = $this->createArticle();
		$newarticle2 = $this->createArticlesencond();

		$article = $this->getArticleService()->findAllArticles();
		$this->assertEquals('2',count($article));
	}

	// public function testfindArticlesByIds()
	// {
	// 	$newarticle = $this->createArticle();
	// 	$ids = array(
	// 		'1'=>'1',
	// 		);
	// 	$findArticles = $this->getArticleService()->findArticlesByIds($ids);
	// 	var_dump($findAllArticles);exit();
	// }

	public function testsearchArticles()
	{
		$newArticle = $this->createArticle();
		$newarticle2 = $this->createArticlesencond();
		$conditions = array(
            'status' => 'published'
        );
		$result = $this->getArticleService()->searchArticles($conditions,'published',0,20);
		$this->assertEquals('2',count($result));
	}

	public function testsearchArticlesCount()
	{
		$newArticle = $this->createArticle();
		$newarticle2 = $this->createArticlesencond();
		$conditions = array(
            'status' => 'published'
        );
		$result = $this->getArticleService()->searchArticlesCount($conditions,'published',0,20);
		$this->assertEquals('2',$result);
	}
	public function testcreateArticle()
	{
		$fileds = array(
			'publishedTime' => 'now',
			'title' => 'test article',
			'type' => 'article',
			'body'=>'正午时分',
			'thumb'=>'thumb',
			'originalThumb'=>'originalThumb',
			'categoryId'=>'1',
			'source'=>'http://www.edusoho.com',
			'sourceUrl'=>'http://www.edusoho.com',
			'tags'=>'default',
		);
		$article = $this->getArticleService()->createArticle($fileds);
		$this->assertEquals('test article',$article['title']);
	}

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
	public function testcreateEmptyArticle()
	{
		$article = null;
		$article = $this->getArticleService()->createArticle($article);
	}

	public function testupdateArticle()
	{
		$newArticle = $this->createArticle();
		$fields = array(
			'publishedTime' => 'now',
			'title' => 'test article2',
			'type' => 'article2',
			'body'=>'正午时分2',
			'thumb'=>'thumb',
			'originalThumb'=>'originalThumb',
			'categoryId'=>'1',
			'source'=>'http://www.edusoho.com',
			'sourceUrl'=>'http://www.edusoho.com',
			'tags'=>'default',
		);
		$article = $this->getArticleService()->updateArticle($newArticle['id'],$fields);
		$this->assertEquals('正午时分2',$article['body']);

	}

	/**
     * @expectedException Topxia\Service\Common\ServiceException
     */
	public function testupdateEmptyArticle()
	{
		$article = null;
		$article = $this->getArticleService()->updateArticle($article['id'],$article);
	}

	public function testhitArticle()
	{
		$newArticle = $this->createArticle();
		$num =5;
		while ($num <= 10) {
			$this->getArticleService()->hitArticle($newArticle['id']);
			$num = $num + 1;
		}		
		$getArticle = $this->getArticleService()->getArticle($newArticle['id']);
		$this->assertEquals('6',$getArticle['hits']);
	}

	/**
     * @expectedException Topxia\Service\Common\ServiceException
     */
	public function testhitEmptyArticle()
	{
		$article = null;
		$article = $this->getArticleService()->hitArticle($article['id']);
	}

	public function testgetArticleLike()
	{		
		$user = $this->createCurrentUser();
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
		$user = $this->createCurrentUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

		$newArticle = $this->createArticle();
		$this->getArticleService()->like($newArticle['id']);

		$like = $this->getArticleService()->getArticleLike($newArticle['id'], $currentUser['id']);
		$this->assertNotNull($like);
	}

	/**
     * @expectedException Topxia\Service\Common\ServiceException
     */
	public function testlikeWithEmptyContent()
	{
		$newArticle = null;
		$this->getArticleService()->like($newArticle['id']);
	}


	// public function testlikeTwich()
	// {
	// 	$newArticle = $this->createArticle();
	// 	$this->getArticleService()->like($newArticle['id']);
	// 	$this->getArticleService()->like($newArticle['id']);
	// }

	public function testcancelLike()
	{
		$user = $this->createCurrentUser();
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
		$property = 'promoted';
		$newArticle = $this->createArticle();
    	$this->getArticleService()->setArticleProperty($newArticle['id'],$property);
    	$getArticle = $this->getArticleService()->getArticle($newArticle['id']);
    	$this->assertEquals('1',$getArticle['promoted']);

	}

    public function testcancelArticleProperty()
    {
    	$property = 'promoted';
		$newArticle = $this->createArticle();
    	$this->getArticleService()->setArticleProperty($newArticle['id'],$property);
    	$getArticle = $this->getArticleService()->getArticle($newArticle['id']);
    	$this->assertEquals('1',$getArticle['promoted']);
    	$this->getArticleService()->cancelArticleProperty($newArticle['id'],$property);
    	$getArticle = $this->getArticleService()->getArticle($newArticle['id']);
    	$this->assertEquals('0',$getArticle['promoted']);
    }

    // public function testtrashArticle()
    // {
    // 	$newArticle = $this->createArticle();
    // 	$this->getArticleService()->transhArticle($newArticle['id']);
    // 	$getArticle = $this->getArticleService()->getArticle($newArticle['id']);
    // 	var_dump($getArticle);exit();
    // }

    // public function testremoveArticlethumb()
    // {
    // }

    // public function testcount()
    // {
    // 	$newArticle = $this->createArticle();
    // 	$a = $this->getArticleService()->count($newArticle['id'], 'upsNum', -1);
    // 	var_dump($a);exit();
    // }

    public function testpublishArticle()
    {
    	$newArticle = $this->createArticle();
		$this->getArticleService()->publishArticle($newArticle['id']);
		$getArticle = $this->getArticleService()->getArticle($newArticle['id']);
		$this->assertGreaterThan(0,$getArticle['publishedTime']);
		$this->assertEquals($getArticle['status'],'published');
    }

    public function testunpublishArticle()
    {
    	$newArticle = $this->createArticle();
		$this->getArticleService()->unpublishArticle($newArticle['id']);
		$getArticle = $this->getArticleService()->getArticle($newArticle['id']);
		$this->assertEquals($getArticle['status'],'unpublished');
    }

    // public function changeIndexPicture($options);

    public function findPublishedArticlesByTagIdsAndCount()
    {		 
		$newArticle = $this->createArticle();
		$this->getArticleService()->publishArticle($newArticle['id']);  
		$getArticle = $this->getArticleService()->getArticle($newArticle['id']);

		$result = $this->getArticleService()->findPublishedArticlesByTagIdsAndCount();	
    }

	protected function createArticle()
	{
		$fileds = array(
			'publishedTime' => 'now',
			'title' => 'test article',
			'type' => 'article',
			'body'=>'正午时分',
			'thumb'=>'thumb',
			'originalThumb'=>'originalThumb',
			'categoryId'=>'1',
			'source'=>'http://www.edusoho.com',
			'sourceUrl'=>'http://www.edusoho.com',
			'tags'=>'default',
			'tagIds'=>'default',
		);
		$article = $this->getArticleService()->createArticle($fileds);
		return $article;
	}

	protected function createArticlesencond()
	{
		$fileds = array(
			'publishedTime' => 'now',
			'title' => 'test article2',
			'type' => 'article2',
			'body'=>'正午时分2',
			'thumb'=>'thumb2',
			'originalThumb'=>'originalThumb2',
			'categoryId'=>'2',
			'source'=>'http://try6.edusoho.cn',
			'sourceUrl'=>'http://try6.edusoho.cn',
			'tags'=>'default',
		);
		$article = $this->getArticleService()->createArticle($fileds);
		return $article;
	}

	protected function createUser($user)
    {
        $userInfo = array();
        $userInfo['email'] = "{$user}@{$user}.com";
        $userInfo['nickname'] = "{$user}";
        $userInfo['password']= "{$user}";
        $userInfo['loginIp'] = '127.0.0.1';
        return $this->getUserService()->register($userInfo);
    }

    private function createCurrentUser()
    {
        $user = array();
        $user['email'] = "user@user.com";
        $user['nickname'] = "user";
        $user['password'] = "user";
        $user =  $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER');
        return $user;
    }

	protected function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
