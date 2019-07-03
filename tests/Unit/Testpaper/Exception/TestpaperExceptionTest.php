<?php

namespace Tests\Unit\OrderFacade\Exception;

use Biz\BaseTestCase;
use Biz\Testpaper\TestpaperException;

class TestpaperExceptionTest extends BaseTestCase
{
    public function testErrorCoinAmount()
    {
        $exception = TestpaperException::NOTFOUND_TESTPAPER();

        $this->assertEquals('exception.testpaper.not_found', $exception->getMessage());
    }

    public function testDraftTestpaper()
    {
        $exception = TestpaperException::DRAFT_TESTPAPER();

        $this->assertEquals('exception.testpaper.draft', $exception->getMessage());
    }

    public function testTestpaperClose()
    {
        $exception = TestpaperException::CLOSED_TESTPAPER();

        $this->assertEquals('exception.testpaper.closed', $exception->getMessage());
    }

    public function testForbiddenResit()
    {
        $exception = TestpaperException::FORBIDDEN_RESIT();

        $this->assertEquals('exception.testpaper.forbidden_resit', $exception->getMessage());
    }

    public function testForbiddenAccessTestpaper()
    {
        $exception = TestpaperException::FORBIDDEN_ACCESS_TESTPAPER();

        $this->assertEquals('exception.testpaper.forbidden_access_testpaper', $exception->getMessage());
    }

    public function testForbiddenDuplicateCommit()
    {
        $exception = TestpaperException::FORBIDDEN_DUPLICATE_COMMIT();

        $this->assertEquals('exception.testpaper.forbidden_duplicate_commit_testpaper', $exception->getMessage());
    }

    public function testReviewingTestpaper()
    {
        $exception = TestpaperException::REVIEWING_TESTPAPER();

        $this->assertEquals('exception.testpaper.reviewing', $exception->getMessage());
    }

    public function testNotTestPaperTask()
    {
        $exception = TestpaperException::NOT_TESTPAPER_TASK();

        $this->assertEquals('exception.testpaper.not_testpaper_task', $exception->getMessage());
    }

    public function testNotFoundExercise()
    {
        $exception = TestpaperException::NOTFOUND_EXERCISE();

        $this->assertEquals('exception.testpaper.not_found_exercise', $exception->getMessage());
    }

    public function testStatusInvalid()
    {
        $exception = TestpaperException::STATUS_INVALID();

        $this->assertEquals('exception.testpaper.status_invalid', $exception->getMessage());
    }

    public function testNotFoundResult()
    {
        $exception = TestpaperException::NOTFOUND_RESULT();

        $this->assertEquals('exception.testpaper.not_found_result', $exception->getMessage());
    }

    public function testModifyCommittedTestpaper()
    {
        $exception = TestpaperException::MODIFY_COMMITTED_TESTPAPER();

        $this->assertEquals('exception.testpaper.modify_committed_testpaper', $exception->getMessage());
    }

    public function testDoingTestpaper()
    {
        $exception = TestpaperException::DOING_TESTPAPER();

        $this->assertEquals('exception.testpaper.doing', $exception->getMessage());
    }

    public function testRedoIntervalExist()
    {
        $exception = TestpaperException::REDO_INTERVAL_EXIST();

        $this->assertEquals('exception.testpaper.redo_interval_exist', $exception->getMessage());
    }
}
