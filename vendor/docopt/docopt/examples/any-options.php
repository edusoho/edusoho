<?php
require __DIR__.'/../src/docopt.php';

$doc = <<<'DOCOPT'
Example of program which uses [options] shortcut in pattern.

Usage:
  any_options_example.py [options] <port>

Options:
  -h --help                show this help message and exit
  --version                show version and exit
  -n, --number N           use N as a number
  -t, --timeout TIMEOUT    set timeout TIMEOUT seconds
  --apply                  apply changes to database
  -q                       operate in quiet mode

DOCOPT;

$result = Docopt::handle($doc, array('version'=>'1.0.0rc2'));
foreach ($result as $k=>$v)
    echo $k.': '.json_encode($v).PHP_EOL;
