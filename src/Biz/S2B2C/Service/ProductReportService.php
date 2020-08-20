<?php

namespace Biz\S2B2C\Service;

interface ProductReportService
{
    const TYPE_JOIN_COURSE = 'join_course';

    const TYPE_REFUND = 'refund';

    const STATUS_CREATED = 'created';

    const STATUS_SENT = 'sent';

    const STATUS_SUCCEED = 'succeed';

    const STATUS_FAILED = 'failed';

    public function create($fields);

    public function getById($id);

    public function updateFailedReason($id, $reason);

    public function updateStatusToSent($id);

    public function updateStatusToSucceed($id);
}