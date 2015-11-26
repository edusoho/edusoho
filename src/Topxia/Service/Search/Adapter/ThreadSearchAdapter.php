<?php
namespace Topxia\Service\Search\Adapter;

class ThreadSearchAdapter extends AbstractSearchAdapter
{
    public function adapt(array $threads)
    {
        $adaptResult = array();

        foreach ($threads as $index => $thread) {
            array_push($adaptResult, $thread);
        }

        return $adaptResult;
    }
}
