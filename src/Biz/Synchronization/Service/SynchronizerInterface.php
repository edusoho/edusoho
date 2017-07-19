<?php

namespace Biz\Synchronization\Service;

interface SynchronizerInterface
{
    public function syncWhenCreated($sourceId);

    public function syncWhenUpdated($sourceId);

    public function syncWhenDeleted($sourceId);
}
