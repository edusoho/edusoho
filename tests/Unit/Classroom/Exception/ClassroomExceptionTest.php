<?php

namespace Tests\Unit\Classroom\Exception;

use Biz\BaseTestCase;
use Biz\Classroom\ClassroomException;

class ClassroomExceptionTest extends BaseTestCase
{
    public function testNotFoundClassroom()
    {
        $exception = ClassroomException::NOTFOUND_CLASSROOM();

        $this->assertEquals('exception.classroom.not_found', $exception->getMessage());
    }

    public function testForbiddenManageClassroom()
    {
        $exception = ClassroomException::FORBIDDEN_MANAGE_CLASSROOM();

        $this->assertEquals('exception.classroom.forbidden_manage_classroom', $exception->getMessage());
    }

    public function testForbiddenTakeClassroom()
    {
        $exception = ClassroomException::FORBIDDEN_TAKE_CLASSROOM();

        $this->assertEquals('exception.classroom.forbidden_take_classroom', $exception->getMessage());
    }

    public function testForbiddenHandleClassroom()
    {
        $exception = ClassroomException::FORBIDDEN_HANDLE_CLASSROOM();

        $this->assertEquals('exception.classroom.forbidden_handle_classroom', $exception->getMessage());
    }

    public function testForbiddenLookClassroom()
    {
        $exception = ClassroomException::FORBIDDEN_LOOK_CLASSROOM();

        $this->assertEquals('exception.classroom.forbidden_look_classroom', $exception->getMessage());
    }

    public function testUnpublishClassroom()
    {
        $exception = ClassroomException::UNPUBLISHED_CLASSROOM();

        $this->assertEquals('exception.classroom.unpublished_classroom', $exception->getMessage());
    }

    public function testChainNotRegistered()
    {
        $exception = ClassroomException::CHAIN_NOT_REGISTERED();

        $this->assertEquals('exception.classroom.chain_not_registered', $exception->getMessage());
    }

    public function testUnjoin()
    {
        $exception = ClassroomException::UN_JOIN();

        $this->assertEquals('exception.classroom.unjoin', $exception->getMessage());
    }

    public function testEmptyTitle()
    {
        $exception = ClassroomException::EMPTY_TITLE();

        $this->assertEquals('exception.classroom.empty_title', $exception->getMessage());
    }

    public function testForbiddenUpdateExpiryDate()
    {
        $exception = ClassroomException::FORBIDDEN_UPDATE_EXPIRY_DATE();

        $this->assertEquals('exception.classroom.forbidden_update_expiry_date', $exception->getMessage());
    }

    public function testForbiddenDeleteNotDraft()
    {
        $exception = ClassroomException::FORBIDDEN_DELETE_NOT_DRAFT();

        $this->assertEquals('exception.classroom.forbidden_delete_not_draft', $exception->getMessage());
    }

    public function testNotFoundMember()
    {
        $exception = ClassroomException::NOTFOUND_MEMBER();

        $this->assertEquals('exception.classroom.not_found_member', $exception->getMessage());
    }

    public function testForbiddenBecomeAuditor()
    {
        $exception = ClassroomException::FORBIDDEN_BECOME_AUDITOR();

        $this->assertEquals('exception.classroom.forbidden_become_auditor', $exception->getMessage());
    }

    public function testForbiddenBecomeStudent()
    {
        $exception = ClassroomException::FORBIDDEN_BECOME_STUDENT();

        $this->assertEquals('exception.classroom.forbidden_become_student', $exception->getMessage());
    }

    public function testExpiryValueLimit()
    {
        $exception = ClassroomException::EXPIRY_VALUE_LIMIT();

        $this->assertEquals('exception.classroom.expiry_earlier_than_current', $exception->getMessage());
    }

    public function testForbiddenNotStudent()
    {
        $exception = ClassroomException::FORBIDDEN_NOT_STUDENT();

        $this->assertEquals('exception.classroom.forbidden_not_student', $exception->getMessage());
    }

    public function testForbiddenWave()
    {
        $exception = ClassroomException::FORBIDDEN_WAVE();

        $this->assertEquals('exception.classroom.forbidden_wave', $exception->getMessage());
    }

    public function testDulicateJoin()
    {
        $exception = ClassroomException::DUPLICATE_JOIN();

        $this->assertEquals('exception.classroom.duplicate_join', $exception->getMessage());
    }

    public function testRecommandRequiredNumeric()
    {
        $exception = ClassroomException::RECOMMEND_REQUIRED_NUMERIC();

        $this->assertEquals('exception.classroom.recommend_required_numeric', $exception->getMessage());
    }

    public function testMemberLevelLimit()
    {
        $exception = ClassroomException::MEMBER_LEVEL_LIMIT();

        $this->assertEquals('exception.classroom.member_level_limit', $exception->getMessage());
    }

    public function testMemberNotInClassroom()
    {
        $exception = ClassroomException::MEMBER_NOT_IN_CLASSROOM();

        $this->assertEquals('exception.classroom.member_not_in_classroom', $exception->getMessage());
    }

    public function testClosedClassroom()
    {
        $exception = ClassroomException::CLOSED_CLASSROOM();

        $this->assertEquals('exception.classroom.closed_classroom', $exception->getMessage());
    }

    public function testUnbuyableClassroom()
    {
        $exception = ClassroomException::UNBUYABLE_CLASSROOM();

        $this->assertEquals('exception.classroom.unbuyable_classroom', $exception->getMessage());
    }

    public function testExpiredClassroom()
    {
        $exception = ClassroomException::EXPIRED_CLASSROOM();

        $this->assertEquals('exception.classroom.expired_classroom', $exception->getMessage());
    }

    public function testForbiddenAuditorLearn()
    {
        $exception = ClassroomException::FORBIDDEN_AUDITOR_LEARN();

        $this->assertEquals('exception.classroom.forbidden_auditor_learn', $exception->getMessage());
    }

    public function testExpiredMember()
    {
        $exception = ClassroomException::EXPIRED_MEMBER();

        $this->assertEquals('exception.classroom.expired_member', $exception->getMessage());
    }

    public function testForbiddenCreateThreadEvent()
    {
        $exception = ClassroomException::FORBIDDEN_CREATE_THREAD_EVENT();

        $this->assertEquals('exception.classroom.forbidden_create_thread_event', $exception->getMessage());
    }
}
