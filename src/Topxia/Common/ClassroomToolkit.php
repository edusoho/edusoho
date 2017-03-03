<?php   
namespace Topxia\Common;

class ClassroomToolkit
{
    public static function buildMemberDeadline($classroom)
    {
        $deadline = $classroom['expiryValue'];

        if ($classroom['expiryMode'] == 'days') {
            $deadline = time() + $classroom['expiryValue'] * 24 * 60 * 60;
        }

        return $deadline;
    }
}
