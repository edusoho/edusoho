<?php

namespace Topxia\Service\User;

interface TrackService
{
    function track($action, $target = null, $note = null);
}