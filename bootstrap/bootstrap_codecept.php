<?php

if (class_exists('PHPUnit_TextUI_ResultPrinter') && !class_exists('PHPUnit\TextUI\ResultPrinter')) {
    class_alias('PHPUnit_TextUI_ResultPrinter', 'PHPUnit\TextUI\ResultPrinter');
    class_alias('PHPUnit_TextUI_TestRunner', 'PHPUnit\TextUI\TestRunner');
}
