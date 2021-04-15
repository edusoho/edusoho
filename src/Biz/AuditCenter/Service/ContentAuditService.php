<?php

namespace Biz\AuditCenter\Service;

interface ContentAuditService
{
    public function getAudit($id);

    public function createAudit($fields);

    public function updateAudit($id, $fields);

    public function deleteAudit($id);

    public function getAuditRecord($id);

    public function createAuditRecord($fields);

    public function updateAuditRecord($id, $fields);
}
