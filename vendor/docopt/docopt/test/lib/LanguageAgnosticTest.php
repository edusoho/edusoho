<?php
namespace Docopt\Test;

class LanguageAgnosticTest implements \PHPUnit_Framework_Test, \PHPUnit_Framework_SelfDescribing
{
	public static function createSuite($testFile)
	{
		if (!file_exists($testFile)) {
			throw new \InvalidArgumentException("Test file $testFile does not exist");
        }
		
		$suite = new \PHPUnit_Framework_TestSuite;
		
		$raw = file_get_contents($testFile);
		$raw = trim(preg_replace("/#.*$/m", "", $raw));
		if (strpos($raw, '"""')===0) {
			$raw = substr($raw, 3);
		}
		
		$idx = 1;
		foreach (explode('r"""', $raw) as $fixture) {
			if (!$fixture) {
				continue;
			}
			
			$name = '';
			$nIdx = 1;
			
			$parts = explode('"""', $fixture, 2);
            if (!isset($parts[1])) {
                throw new \Exception("Missing string close");
            }
            list ($doc, $body) = $parts;

			$cases = array();
			foreach (array_slice(explode('$', $body), 1) as $case) {
				$case = trim($case);
				list ($argv, $expect) = explode("\n", $case, 2);
				$expect = json_decode($expect, true);
				
				$argx = explode(' ', $argv, 2);
				$prog = $argx[0];
				$argv = isset($argx[1]) ? $argx[1] : "";
				
				$tName = $name ? ($name.$nIdx) : 'unnamed'.$idx;
                $test = new self($tName, $doc, $prog, $argv, $expect);
				$suite->addTest($test);
				$idx++;
			}
		}
		
		return $suite;
	}

    /** @var string */
    private $name;

    /** @var string */
    private $doc;

    /** @var string */
    private $prog;

    /** @var string[] */
    private $argv;

    /** @var string[]|string */
    private $expect;

	public function __construct($name, $doc, $prog, $argv, $expect)
	{
		$this->doc = $doc;
		$this->name = $name;
		$this->prog = $prog;
		$this->argv = $argv;
		
		if ($expect == "user-error") {
			$expect = array('user-error');
        }
		
		$this->expect = $expect;
	}
	
	public function run(\PHPUnit_Framework_TestResult $result=null)
    {
        if (!$result) {
            $result = new \PHPUnit_Framework_TestResult();
        }
        
        $opt = null;
		
		\PHP_Timer::start();
		$result->startTest($this);
		
		try {
		    $opt = \Docopt::handle($this->doc, array('argv'=>$this->argv, 'exit'=>false));
		}
		catch (\Exception $ex) {
			// gulp
		}
		
		$found = null;
		if ($opt) {
		    if (!$opt->success) {
		        $found = array('user-error');
		    } elseif (empty($opt->args)) {
		        $found = array();
		    } else {
		        $found = $opt->args;
		    }
		}
		
		$time = \PHP_Timer::stop();
		try {
        	\PHPUnit_Framework_Assert::assertEquals($this->expect, $found);
		}
        catch (\PHPUnit_Framework_AssertionFailedError $e) {
            $result->addFailure($this, $e, $time);
        }
        
		$result->endTest($this, $time);

        return $result;
    }
    
    public function count()
    {
        return 1;
    }
    
    public function toString()
    {
    	return __CLASS__.'::'.$this->name.' - "'.$this->prog.($this->argv ? ' '.$this->argv : '').'"';
    }
}
