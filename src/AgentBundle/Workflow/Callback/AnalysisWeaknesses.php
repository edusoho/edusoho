<?php

namespace AgentBundle\Workflow\Callback;

use AgentBundle\Workflow\AbstractWorkflow;

class AnalysisWeaknesses extends AbstractWorkflow
{
    public function execute($inputs)
    {
        $this->getAIService()->pushMessage([
            'domainId' => $inputs['domainId'],
            'userId' => $inputs['userId'],
            'contentType' => 'text',
            'content' => $this->makeMarkdown($inputs),
            'push' => [
                'title' => '推荐学习',
                'content' => '刚才的答题结果✍️已帮你分析出薄弱知识点，快来看看自己哪方面知识需要加强👉 ',
            ],
        ]);
    }

    private function makeMarkdown($inputs)
    {
        $user = $this->getUserService()->getUser($inputs['userId']);
        $markdown = "hi，{$user['nickname']}同学，恭喜完成答题，根据此次答题结果分析，当前掌握较为薄弱的知识点是：  \n";
        foreach ($inputs['keypoints'] as $key => $keypoint) {
            $seq = $key + 1;
            $markdown .= "{$seq}. $keypoint\n";
        }
        if (empty($inputs['documents'])) {
            return $markdown;
        }
        $markdown .= "\n推荐以下学习知识点的相关课程任务：  \n";
        $tasks = $this->getTaskService()->findTasksByActivityIds(array_column($inputs['documents'], 'extId'));
        foreach ($inputs['documents'] as $key => $document) {
            $seq = $key + 1;
            $markdown .= "* [任务{$seq}: {$document['name']}](/course/{$document['dataset']['extId']}/task/{$tasks[$document['extId']]['id']})\n";
        }

        return $markdown;
    }
}
