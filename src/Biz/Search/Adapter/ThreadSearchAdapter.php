<?php

namespace Biz\Search\Adapter;

class ThreadSearchAdapter extends AbstractSearchAdapter
{
    public function adapt(array $threads)
    {
        $adaptResult = array();

        foreach ($threads as $index => $thread) {
            $thread['id'] = $thread['threadId'];
            array_push($adaptResult, $thread);
        }

        return $adaptResult;
    }
}
