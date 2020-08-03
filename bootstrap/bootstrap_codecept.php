<?php

if (class_exists('PHPUnit_TextUI_ResultPrinter') && !class_exists('PHPUnit\TextUI\ResultPrinter')) {
    class_alias('PHPUnit_TextUI_ResultPrinter', 'PHPUnit\TextUI\ResultPrinter');
    class_alias('PHPUnit_TextUI_TestRunner', 'PHPUnit\TextUI\TestRunner');
}
if (class_exists('PHPUnit_Runner_Filter_Factory') && !class_exists('PHPUnit\Runner\Filter\Factory')) {
    class_alias('PHPUnit_Runner_Filter_Factory', 'PHPUnit\Runner\Filter\Factory');
    class_alias('PHPUnit_Runner_Filter_Test', 'PHPUnit\Runner\Filter\NameFilterIterator');
    class_alias('PHPUnit_Runner_Filter_Group_Include', 'PHPUnit\Runner\Filter\IncludeGroupFilterIterator');
    class_alias('PHPUnit_Runner_Filter_Group_Exclude', 'PHPUnit\Runner\Filter\ExcludeGroupFilterIterator');
//    class_alias('PHPUnit_Runner_Version', 'PHPUnit\Runner\Version');
}
