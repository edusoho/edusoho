<?php
namespace Topxia\Service\Search\Adapter;

class ThreadSearchAdapter extends AbstractSearchAdapter
{
    public function adapt(array $threads)
    {
        $adaptResult = array();

        foreach ($threads as $index => $thread) {
            $thread['id'] = $thread['threadId'];

            if ($thread['targetType'] == 'group') {
                $thread['groupId']      = $thread['targetId'];
                $thread['isStick']      = 0;
                $thread['isElite']      = 0;
                $thread['lastPostTime'] = 0;
            }

            array_push($adaptResult, $thread);
        }

        return $adaptResult;
    }
}
