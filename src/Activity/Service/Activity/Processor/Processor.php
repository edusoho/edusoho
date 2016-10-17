<?php

namespace Activity\Service\Activity\Processor;

interface Processor
{
    public function create($fields);

    public function update($mediaId, $fields);

    public function delete($mediaId);

    public function get($mediaId);
}
