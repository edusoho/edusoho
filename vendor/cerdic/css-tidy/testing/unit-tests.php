<?php

/**@file
 * Script for unit testing, allows for more fine grained error reporting
 * when things go wrong.
 * @author Edward Z. Yang <admin@htmlpurifier.org>
 *
 */

error_reporting(E_ALL);

// Configuration
if (file_exists('../test-settings.php')) include_once '../test-settings.php';

// Includes
require_once '../vendor/autoload.php';
require_once 'unit-tests/class.csstidy_reporter.php';
require_once 'unit-tests/class.csstidy_harness.php';
require_once 'unit-tests.inc';

// Test files
$test_files = array();
require 'unit-tests/_files.php';

// Setup test files
$test = new TestSuite('CSSTidy unit tests');
foreach ($test_files as $test_file) {
	require_once "unit-tests/$test_file";
	list($x, $class_suffix) = explode('.', $test_file);
	$test->add("csstidy_test_$class_suffix");
}

if (SimpleReporter::inCli()) $reporter = new TextReporter();
else $reporter = new csstidy_reporter('UTF-8');

exit ($test->run($reporter) ? 0 : 1);
