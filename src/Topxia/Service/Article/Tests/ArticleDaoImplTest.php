<?php
namespace Topxia\Service\Cash\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\ServiceException;


class ArticleDaoImplTest extends BaseTestCase
{

    public function testFindPublishedArticlesByTagIdsAndCount()
    {
       $articles = $this->getArticleDao()->findPublishedArticlesByTagIdsAndCount(array(1, 2, 4, 3), 10);
    }

    protected function getArticleDao()
    {
        return $this->getServiceKernel()->createDao('Article.ArticleDao');
    }

}
