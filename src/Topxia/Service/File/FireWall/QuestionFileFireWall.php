<?php

namespace Topxia\Service\FIle\FireWall;

use Topxia\Service\Common\ServiceKernel;

class QuestionFileFireWall
{
    public function canAccess($attachment)
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return true;
        }
        $question = $this->getQuestionService()->getQuestion($attachment['targetId']);
        if ($user['id'] == $question['userId']) {
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

    protected function getQuestionService()
    {
        return $this->getKernel()->createService('Question.QuestionService');
    }
}
