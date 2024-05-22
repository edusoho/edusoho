<?php

namespace Biz\AI\Service;

interface AIService
{
    public function inspectAccount();

    public function enableAccount();

    public function disableAccount();

    public function generateAnswer($app, $inputs);

    public function stopGeneratingAnswer($app, $messageId, $taskId);

    public function needGenerateNewAnswer($app, $inputs);

    public function getAnswerFromLocal($app, $inputs);
}
