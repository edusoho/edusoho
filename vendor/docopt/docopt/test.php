<?php
if (php_sapi_name() != 'cli') {
    throw new \Exception();
}

$basePath = __DIR__;
$testPath = __DIR__.'/test';
require $basePath.'/vendor/autoload.php';

require $testPath.'/lib/TestCase.php';
require $testPath.'/lib/LanguageAgnosticTest.php';
require $testPath.'/lib/PythonPortedTest.php';

$options = array(
    'filter'        => null,
    'coverage-html' => null,
);
$options = array_merge(
    $options,
    getopt('', array('filter:', 'coverage-html:'))
);

$pyTestFile = $basePath.'/py/testcases.docopt';
if (!file_exists($pyTestFile)) {
    die("Please ensure you have loaded the git submodules\n");
}

$suite = new PHPUnit_Framework_TestSuite();
$suite->addTest(new PHPUnit_Framework_TestSuite('Docopt\Test\PythonPortedTest'));
$suite->addTest(Docopt\Test\LanguageAgnosticTest::createSuite($pyTestFile));
$suite->addTest(Docopt\Test\LanguageAgnosticTest::createSuite("$basePath/test/extra.docopt"));

$args = array(
    'filter'=>$options['filter'],
    'strict'=>true,
    'processIsolation'=>false,
    'backupGlobals'=>false,
    'backupStaticAttributes'=>false,
    'convertErrorsToExceptions'=>true,
    'convertNoticesToExceptions'=>true,
    'convertWarningsToExceptions'=>true,
    'addUncoveredFilesFromWhitelist'=>true,
    'processUncoveredFilesFromWhitelist'=>true,
);
if ($options['coverage-html']) {
    $args['coverageHtml'] = $options['coverage-html'];
}

$runner = new PHPUnit_TextUI_TestRunner();
$runner->doRun($suite, $args);

