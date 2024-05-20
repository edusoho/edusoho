<?php

namespace Biz\AI\Service;

interface AIService
{
    public function generateAnswer($app, $inputs);

    public function stopGeneratingAnswer($messageId, $taskId);

    public function needGenerateNewAnswer($app, $inputs);

    public function getAnswerFromLocal($app, $inputs);
}
