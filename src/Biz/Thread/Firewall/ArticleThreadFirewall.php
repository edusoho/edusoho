<?php

namespace Biz\Thread\Firewall;

use Topxia\Service\Common\ServiceKernel;

class ArticleThreadFirewall
{
    public function accessPostCreate($post)
    {
        $user = $this->getCurrentUser();

        if ($user->isLogin()) {
            return true;
        }

        return false;
    }

    public function accessPostDelete($post)
    {
        $user = $this->getCurrentUser();

        if ($user->isLogin()) {
            $post = $this->getThreadService()->getPost($post['id']);

            if ($post['userId'] == $user['id'] || $user->isAdmin()) {
                return true;
            }
        }

        return false;
    }

    public function accessPostVote($post)
    {
        $user = $this->getCurrentUser();

        if ($user->isLogin()) {
            return true;
        }

        return false;
    }

    protected function getArticleService()
    {
        return $this->getKernel()->createService('Article:ArticleService');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }

    public function getCurrentUser()
    {
        return $this->getKernel()->getCurrentUser();
    }

    protected function getThreadService()
    {
        return $this->getKernel()->createService('Thread:ThreadService');
    }
}
