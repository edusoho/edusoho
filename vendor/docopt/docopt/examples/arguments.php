<?php
require __DIR__.'/../src/docopt.php';

$doc = <<<'DOCOPT'
Process FILE and optionally apply correction to either left-hand side or
right-hand side.

Usage: arguments_example.py [-vqrh] [FILE] ...
       arguments_example.py (--left | --right) CORRECTION FILE

Arguments:
  FILE        optional input file
  CORRECTION  correction angle, needs FILE, --left or --right to be present

Options:
  -h --help
  -v       verbose mode
  -q       quiet mode
  -r       make report
  --left   use left-hand side
  --right  use right-hand side

DOCOPT;

$result = Docopt::handle($doc, array('version'=>'1.0.0rc2'));
foreach ($result as $k=>$v)
    echo $k.': '.json_encode($v).PHP_EOL;
