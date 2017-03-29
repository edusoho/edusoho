<?php

class QuestionFavoriteMigrate extends AbstractMigrate
{
    public function update($page)
    {
        if (!$this->isFieldExist('question_favorite', 'targetType')) {
            $this->getConnection()->exec("
                ALTER TABLE question_favorite ADD targetType VARCHAR(50) NOT NULL DEFAULT '' AFTER `questionId`
            ");
        }

        if (!$this->isFieldExist('question_favorite', 'targetId')) {
            $this->getConnection()->exec("
                ALTER TABLE question_favorite ADD targetId INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `targetType`
            ");
        }

        $nextPage = $this->updateQuestionFavorite($page);
        if (!empty($nextPage)) {
            return $nextPage;
        }
    }

    private function updateQuestionFavorite($page)
    {
        $sql = "SELECT * FROM question_favorite ORDER BY id LIMIT 0, {$this->perPageCount};";
        $favorites = $this->getConnection()->fetchAll($sql);

        foreach ($favorites as $favorite) {
            $targetArr = explode('-', $favorite['target']);

            $sql = "UPDATE question_favorite set targetId = {$targetArr[1]},targetType='".$targetArr[0]."' WHERE id = {$favorite['id']}";
            $this->getConnection()->exec($sql);
        }

        return $page+1;
    }
}
