<?php

namespace Topxia\Service\FIle\FireWall;

use Topxia\Service\Common\ServiceKernel;

class ArticleFileFireWall
{
    public function canAccess($attachment)
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return true;
        }
        $article = $this->getArticleService()->getArticle($attachment['targetId']);
        if ($article['userId'] == $user['id']) {
            return true;
        }
        return false;
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }

    public function getCurrentUser()
    {
        return $this->getKernel()->getCurrentUser();
    }

    protected function getArticleService()
    {
        return $this->getKernel()->createService('Article.ArticleService');
    }
}
