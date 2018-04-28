<?php

namespace Biz\File\FireWall;

class QuestionFileFireWall extends BaseFireWall implements FireWallInterface
{
    public function canAccess($attachment)
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return true;
        }

        if ($attachment['targetType'] == 'question.answer') {
            $itemResult = $this->getTestpaperService()->getItemResult($attachment['targetId']);
            if ($itemResult) {
                return true;
            }
        } else {
            $question = $this->getQuestionService()->get($attachment['targetId']);
            if ($user['id'] == $question['userId']) {
                return true;
            }
        }

        return false;
    }

    protected function getQuestionService()
    {
        return $this->biz->service('Question:QuestionService');
    }

    protected function getTestpaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }
}
