<?php   
namespace Topxia\Common;

class ClassroomToolkit
{
    public static function buildMemberDeadlineByMode($fields, $type)
    {
        $deadline = $fields['expiryValue'];

        if ($type == 'days') {
            $deadline = time() + $fields['expiryValue'] * 24 * 60 * 60;
        }

        if ($type == 'date') {
            if (!is_int($deadline)) {
                $deadline = strtotime($deadline.' 23:59:59');
            }
            if ($deadline < time()) {
                throw $this->createServiceException($this->getKernel()->trans('有效期的设置时间小于当前时间！'));
            }
        }

        return $deadline;
    }
}
