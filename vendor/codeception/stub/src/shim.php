<?php
if (!class_exists('PHPUnit\Framework\TestCase') && class_exists('PHPUnit_Framework_TestCase')) {
    class_alias('PHPUnit_Framework_TestCase', 'PHPUnit'.'\Framework\TestCase');
}

if (!class_exists('PHPUnit\Runner\Version')) {
    class_alias('PHPUnit_Runner_Version', 'PHPUnit\Runner\Version');
}
if (class_exists('PHPUnit_Framework_MockObject_Generator')) {
    class_alias('PHPUnit_Framework_MockObject_Generator', 'PHPUnit\Framework\MockObject\Generator');
    class_alias('PHPUnit_Framework_MockObject_InvocationMocker', 'PHPUnit\Framework\MockObject\InvocationMocker');
    class_alias('PHPUnit_Framework_MockObject_Invokable', 'PHPUnit\Framework\MockObject\Invokable');
    class_alias('PHPUnit_Framework_MockObject_Matcher', 'PHPUnit\Framework\MockObject\Matcher');
    class_alias('PHPUnit_Framework_MockObject_MockBuilder', 'PHPUnit\Framework\MockObject\MockBuilder');
    if (interface_exists('PHPUnit_Framework_MockObject_MockObject')) {
        /*
         * old name still exists in https://github.com/sebastianbergmann/phpunit-mock-objects/blob/master/src/MockObject.php
         * but namespaced alias is provided by https://github.com/sebastianbergmann/phpunit-mock-objects/blob/master/src/ForwardCompatibility/MockObject.php
         */
        class_alias('PHPUnit_Framework_MockObject_MockObject', 'PHPUnit\Framework\MockObject\MockObject');
    }
    class_alias('PHPUnit_Framework_MockObject_Stub', 'PHPUnit\Framework\MockObject\Stub');
    class_alias('PHPUnit_Framework_MockObject_Verifiable', 'PHPUnit\Framework\MockObject\Verifiable');
    class_alias('PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount', 'PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount');
    class_alias('PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters', 'PHPUnit\Framework\MockObject\Matcher\ConsecutiveParameters');
    class_alias('PHPUnit_Framework_MockObject_Matcher_Invocation', 'PHPUnit\Framework\MockObject\Matcher\Invocation');
    class_alias('PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex', 'PHPUnit\Framework\MockObject\Matcher\InvokedAtIndex');
    class_alias('PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastCount', 'PHPUnit\Framework\MockObject\Matcher\InvokedAtLeastCount');
    class_alias('PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce', 'PHPUnit\Framework\MockObject\Matcher\InvokedAtLeastOnce');
    class_alias('PHPUnit_Framework_MockObject_Matcher_InvokedAtMostCount', 'PHPUnit\Framework\MockObject\Matcher\InvokedAtMostCount');
    class_alias('PHPUnit_Framework_MockObject_Matcher_InvokedCount', 'PHPUnit\Framework\MockObject\Matcher\InvokedCount');
    class_alias('PHPUnit_Framework_MockObject_Matcher_InvokedRecorder', 'PHPUnit\Framework\MockObject\Matcher\InvokedRecorder');
    class_alias('PHPUnit_Framework_MockObject_Matcher_MethodName', 'PHPUnit\Framework\MockObject\Matcher\MethodName');
    class_alias('PHPUnit_Framework_MockObject_Matcher_Parameters', 'PHPUnit\Framework\MockObject\Matcher\Parameters');
    class_alias('PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls', 'PHPUnit\Framework\MockObject\Stub\ConsecutiveCalls');
    class_alias('PHPUnit_Framework_MockObject_Stub_Exception', 'PHPUnit\Framework\MockObject\Stub\Exception');
    class_alias('PHPUnit_Framework_MockObject_Stub_ReturnArgument', 'PHPUnit\Framework\MockObject\Stub\ReturnArgument');
    class_alias('PHPUnit_Framework_MockObject_Stub_ReturnCallback', 'PHPUnit\Framework\MockObject\Stub\ReturnCallback');
    class_alias('PHPUnit_Framework_MockObject_Stub_Return', 'PHPUnit\Framework\MockObject\Stub\ReturnStub');
}
