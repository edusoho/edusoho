<?php

namespace Biz\File\FireWall;

use Topxia\Service\Common\ServiceKernel;

class ArticleFileFireWall extends BaseFireWall implements FireWallInterface
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

    protected function getArticleService()
    {
        return $this->biz->service('Article:ArticleService');
    }
}
