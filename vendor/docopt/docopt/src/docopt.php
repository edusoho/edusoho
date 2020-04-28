<?php
/**
 * Command-line interface parser that will make you smile.
 *
 * - http://docopt.org
 * - Repository and issue-tracker: https://github.com/docopt/docopt.php
 * - Licensed under terms of MIT license (see LICENSE-MIT)
 * - Copyright (c) 2013 Vladimir Keleshev, vladimir@keleshev.com
 *                      Blake Williams, <code@shabbyrobe.org>
 */
namespace
{
    class Docopt
    {
        /**
         * API compatibility with python docopt
         *
         * @param string $doc
         * @param array $params
         * @return Docopt\Response
         */
        static function handle($doc, $params=array())
        {
            $argv = null;
            if (isset($params['argv'])) {
                $argv = $params['argv'];
                unset($params['argv']);
            }
            elseif (is_string($params)) {
                $argv = $params;
                $params = array();
            }
            
            $h = new \Docopt\Handler($params);
            return $h->handle($doc, $argv);
        }
    }
}

namespace Docopt
{
    /**
     * Return true if all cased characters in the string are uppercase and there is 
     * at least one cased character, false otherwise.
     * Python method with no knowrn equivalent in PHP.
     *
     * @param string $string
     * @return bool
     */
    function is_upper($string)
    {
        return preg_match('/[A-Z]/', $string) && !preg_match('/[a-z]/', $string);
    }

    /**
     * Return True if any element of the iterable is true. If the iterable is
     * empty, return False. Python method with no known equivalent in PHP.
     *
     * @param array|\Iterator $iterable
     * @return bool
     */
    function any($iterable)
    {
        foreach ($iterable as $element) {
            if ($element) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * The PHP version of this doesn't support array iterators
     * @param array|\Iterator $input
     * @param callable $callback
     * @param bool $reKey
     * @return array
     */
    function array_filter($input, $callback, $reKey=false)
    {
        if ($input instanceof \Iterator) {
            $input = iterator_to_array($input);
        }
        $filtered = \array_filter($input, $callback);
        if ($reKey) {
            $filtered = array_values($filtered);
        }
        return $filtered;
    }

    /**
     * The PHP version of this doesn't support array iterators
     * @param array $values,...
     * @return array
     */
    function array_merge()
    {
        $values = func_get_args();
        $resolved = array();
        foreach ($values as $v) {
            if ($v instanceof \Iterator) {
                $resolved[] = iterator_to_array($v);
            } else {
                $resolved[] = $v;
            }
        }
        return call_user_func_array('array_merge', $resolved);
    }

    /**
     * @param string $str
     * @param string $test The suffix to check
     * @return bool
     */
    function ends_with($str, $test)
    {
        $len = strlen($test);
        return substr_compare($str, $test, -$len, $len) === 0;
    }

    /**
     * @param mixed $obj
     * @return string
     */
    function get_class_name($obj)
    {
        $cls = get_class($obj);
        return substr($cls, strpos($cls, '\\')+1);
    }
    
    function dumpw($val)
    {
        echo dump($val);
        echo PHP_EOL;
    }

    function dump($val)
    {
        $out = "";
        if (is_array($val) || $val instanceof \Traversable) {
            $out = '[';
            $cur = array();
            foreach ($val as $i) {
                if (is_object($i)) {
                    $cur[] = $i->dump();
                } elseif (is_array($i)) {
                    $cur[] = dump($i);
                } else {
                    $cur[] = dump_scalar($i);
                }
            }
            $out .= implode(', ', $cur);
            $out .= ']';
        }
        elseif ($val instanceof Pattern) {
            $out .= $val->dump();
        } else {
            throw new \InvalidArgumentException();
        }
        return $out;
    }

    function dump_scalar($scalar)
    {
        if ($scalar === null) {
            return 'None';
        } elseif ($scalar === false) {
            return 'False';
        } elseif ($scalar === true) {
            return 'True';
        } elseif (is_int($scalar) || is_float($scalar)) {
            return $scalar;
        } else {
            return "'$scalar'";
        }
    }

    /**
     * Error in construction of usage-message by developer
     */
    class LanguageError extends \Exception
    {
    }

    /**
     * Exit in case user invoked program with incorrect arguments.
     * DocoptExit equivalent.
     */
    class ExitException extends \RuntimeException
    {
        /** @var string */
        public static $usage;
        
        /** @var int */
        public $status;
        
        /**
         * @param ?string $message
         * @param int $status
         */
        public function __construct($message=null, $status=1)
        {
            parent::__construct(trim($message.PHP_EOL.static::$usage));
            $this->status = $status;
        }
    }

    abstract class Pattern
    {
        /** @var Pattern[] */
        public $children = array();
 
        /**
         * @param string[]|string $types
         * @return Pattern[]
         */
        abstract function flat($types=array());

        /**
         * @param Pattern[] $left
         * @param Pattern[] $collected
         */
        abstract function match($left, $collected=null);

        /** @return string */
        function name() { return ''; }

        /** @return string */
        function dump() { return ''; }

        /** @return string */
        public function __toString()
        {
            return serialize($this);
        }

        /** @return string */
        public function hash()
        {
            return (string) crc32((string)$this);
        }
        
        /** @return $this */
        public function fix()
        {
            $this->fixIdentities();
            $this->fixRepeatingArguments();
            return $this;
        }
        
        /**
         * Make pattern-tree tips point to same object if they are equal.
         *
         * @param Pattern[]|null $uniq
         */
        public function fixIdentities($uniq=null)
        {
            if (!isset($this->children) || !$this->children) {
                return $this;
            }
            if ($uniq === null) {
                $uniq = array_unique($this->flat());
            }

            foreach ($this->children as $i=>$child) {
                if (!$child instanceof BranchPattern) {
                    if (!in_array($child, $uniq)) {
                        // Not sure if this is a true substitute for 'assert c in uniq'
                        throw new \UnexpectedValueException();
                    }
                    $this->children[$i] = $uniq[array_search($child, $uniq)];
                }
                else {
                    $child->fixIdentities($uniq);   
                }     
            }
        }
        
        /**
         * Fix elements that should accumulate/increment values.
         * @return $this
         */
        public function fixRepeatingArguments()
        {   
            $either = array();
            foreach (transform($this)->children as $child) {
                $either[] = $child->children;
            }
            
            foreach ($either as $case) {
                $counts = array();
                foreach ($case as $child) {
                    $ser = serialize($child);
                    if (!isset($counts[$ser])) {
                        $counts[$ser] = array('cnt'=>0, 'items'=>array());
                    }
                    
                    $counts[$ser]['cnt']++;
                    $counts[$ser]['items'][] = $child;
                }
                
                $repeatedCases = array();
                foreach ($counts as $child) {
                    if ($child['cnt'] > 1) {
                        $repeatedCases = array_merge($repeatedCases, $child['items']);
                    }
                }
                
                foreach ($repeatedCases as $e) {
                    if ($e instanceof Argument || ($e instanceof Option && $e->argcount)) {
                        if (!$e->value) {
                            $e->value = array();
                        } elseif (!is_array($e->value) && !$e->value instanceof \Traversable) {
                            $e->value = preg_split('/\s+/', $e->value);
                        }
                    }
                    if ($e instanceof Command || ($e instanceof Option && $e->argcount == 0)) {
                        $e->value = 0;
                    }
                }
            }
            
            return $this;
        } 
        
        public function __get($name)
        {
            if ($name == 'name') {
                return $this->name();
            } else {
                throw new \BadMethodCallException("Unknown property $name");
            }
        }
    }

    /**
     * Expand pattern into an (almost) equivalent one, but with single Either.
     * 
     * Example: ((-a | -b) (-c | -d)) => (-a -c | -a -d | -b -c | -b -d)
     * Quirks: [-a] => (-a), (-a...) => (-a -a)
     *
     * @param Pattern $pattern
     * @return Either
     */
    function transform($pattern)
    {
        $result = array();
        $groups = array(array($pattern));
        $parents = array('Required', 'Optional', 'OptionsShortcut', 'Either', 'OneOrMore');

        while ($groups) {
            $children = array_shift($groups);
            $types = array();
            foreach ($children as $c) {
                if (is_object($c)) {
                    $types[get_class_name($c)] = true;
                }
            }

            if (array_intersect(array_keys($types), $parents)) {
                $child = null;
                foreach ($children as $currentChild) {
                    if (in_array(get_class_name($currentChild), $parents)) {
                        $child = $currentChild;
                        break;
                    }
                }
                unset($children[array_search($child, $children)]);
                $childClass = get_class_name($child);
                if ($childClass == 'Either') {
                    foreach ($child->children as $c) {
                        $groups[] = array_merge(array($c), $children);
                    }
                }
                elseif ($childClass == 'OneOrMore') {
                    $groups[] = array_merge($child->children, $child->children, $children);
                }
                else {
                    $groups[] = array_merge($child->children, $children);
                }
            }
            else {
                $result[] = $children;
            }
        }

        $rs = array();
        foreach ($result as $e) {
            $rs[] = new Required($e);
        }
        return new Either($rs);
    }

    abstract class LeafPattern extends Pattern
    {
        /**
         * @param Pattern[] $left
         * @return SingleMatch
         */
        abstract function singleMatch($left);

        /**
         * @param string[]|string $types
         * @return Pattern[]
         */
        public function flat($types=array())
        {
            $types = is_array($types) ? $types : array($types);
            
            if (!$types || in_array(get_class_name($this), $types)) {
                return array($this);
            } else {
                return array();
            }
        }

        /**
         * @param Pattern[] $left
         * @param Pattern[] $collected
         */
        public function match($left, $collected=null)
        {
            if (!$collected) {
                $collected = array();
            }
            
            list ($pos, $match) = $this->singleMatch($left)->toArray();
            if (!$match) {
                return array(false, $left, $collected);
            }
            
            $left_ = $left;
            unset($left_[$pos]);
            $left_ = array_values($left_);
            
            $name = $this->name;
            $sameName = array_filter($collected, function ($a) use ($name) { return $name == $a->name; }, true);
            
            if (is_int($this->value) || is_array($this->value) || $this->value instanceof \Traversable) {
                if (is_int($this->value)) {
                    $increment = 1;
                } else {
                    $increment = is_string($match->value) ? array($match->value) : $match->value;
                }
                
                if (!$sameName) {
                    $match->value = $increment;
                    return array(true, $left_, array_merge($collected, array($match)));
                }
                
                if (is_array($increment) || $increment instanceof \Traversable) {
                    $sameName[0]->value = array_merge($sameName[0]->value, $increment);
                } else {
                    $sameName[0]->value += $increment;
                }
                
                return array(true, $left_, $collected);
            }
            
            return array(true, $left_, array_merge($collected, array($match)));
        }
    }

    class BranchPattern extends Pattern
    {
        /**
         * @param Pattern[]|Pattern $children
         */
        public function __construct($children=null)
        {
            if (!$children) {
                $children = array();
            } elseif ($children instanceof Pattern) {
                $children = func_get_args();
            }
            foreach ($children as $child) {
                $this->children[] = $child;
            }
        }

        /**
         * @param string[]|string $types
         * @return Pattern[]
         */
        public function flat($types=array())
        {
            $types = is_array($types) ? $types : array($types);

            if (in_array(get_class_name($this), $types)) {
                return array($this);
            }
            $flat = array();
            foreach ($this->children as $c) {
                $flat = array_merge($flat, $c->flat($types));
            }
            return $flat;
        }

        /** @return string */
        public function dump()
        {
            $out = get_class_name($this).'(';
            $cd = array();
            foreach ($this->children as $c) {
                $cd[] = $c->dump();
            }
            $out .= implode(', ', $cd).')';
            return $out;
        }

        /**
         * @param Pattern[] $left
         * @param Pattern[] $collected
         */
        public function match($left, $collected=null)
        {
            throw new \RuntimeException("Unsupported");
        }
    }

    class Argument extends LeafPattern
    {
        /* {{{ this stuff is against LeafPattern in the python version 
         * but it interferes with name() */

        /** @var ?string */
        public $name;

        /** @var mixed */
        public $value;
        
        /**
         * @param ?string $name
         * @param mixed $value
         */
        public function __construct($name, $value=null)
        {
            $this->name = $name;
            $this->value = $value;
        }
        /* }}} */
        
        /**
         * @param Pattern[] $left
         * @return SingleMatch
         */
        public function singleMatch($left)
        {
            foreach ($left as $n=>$pattern) {
                if ($pattern instanceof Argument) {
                    return new SingleMatch($n, new Argument($this->name, $pattern->value));
                }
            }
            return new SingleMatch(null, null);
        }

        /**
         * @param string $source
         * @return Argument
         */
        public static function parse($source)
        {
            $name = null;
            $value = null;

            if (preg_match_all('@(<\S*?'.'>)@', $source, $matches)) {
                $name = $matches[0][0];
            }
            if (preg_match_all('@\[default: (.*)\]@i', $source, $matches)) {
                $value = $matches[0][1];
            }
            return new static($name, $value);
        }

        /** @return string */
        public function dump()
        {
            return get_class_name($this)."(".dump_scalar($this->name).", ".dump_scalar($this->value).")";
        }
    }

    class Command extends Argument
    {
        /** @var string */
        public $name;

        public $value;
        
        /**
         * @param string $name
         * @param bool $value
         */
        public function __construct($name, $value=false)
        {
            $this->name = $name;
            $this->value = $value;
        }
        
        /**
         * @param Pattern[] $left
         * @return SingleMatch
         */
        function singleMatch($left)
        {
            foreach ($left as $n=>$pattern) {
                if ($pattern instanceof Argument) {
                    if ($pattern->value == $this->name) {
                        return new SingleMatch($n, new Command($this->name, true));
                    } else {
                        break;
                    }
                }
            }
            return new SingleMatch(null, null);
        }
    }

    class Option extends LeafPattern
    {
        /** @var ?string */
        public $short;

        /** @var ?string */
        public $long;

        /** @var int */
        public $argcount;

        /** @var bool|string|null */
        public $value;

        /**
         * @param ?string $short
         * @param ?string $long
         * @param int $argcount
         * @param bool|string|null $value
         */
        public function __construct($short=null, $long=null, $argcount=0, $value=false)
        {
            if ($argcount != 0 && $argcount != 1) {
                throw new \InvalidArgumentException();
            }
            
            $this->short = $short;
            $this->long = $long;
            $this->argcount = $argcount;
            $this->value = $value;
            
            if ($value === false && $argcount) {
                $this->value = null;
            }
        }

        /**
         * @param string
         */
        public static function parse($optionDescription)
        {
            $short = null;
            $long = null;
            $argcount = 0;
            $value = false;
            
            $exp = explode('  ', trim($optionDescription), 2);
            $options = $exp[0];
            $description = isset($exp[1]) ? $exp[1] : '';
            
            $options = str_replace(',', ' ', str_replace('=', ' ', $options));
            foreach (preg_split('/\s+/', $options) as $s) {
                if (strpos($s, '--')===0) {
                    $long = $s;
                } elseif ($s && $s[0] == '-') {
                    $short = $s;
                } else {
                    $argcount = 1;
                }
            }
            
            if ($argcount) {
                $value = null;
                if (preg_match('@\[default: (.*)\]@i', $description, $match)) {
                    $value = $match[1];
                }
            }

            return new static($short, $long, $argcount, $value);
        }
        
        /**
         * @param Pattern[] $left
         * @return SingleMatch
         */
        public function singleMatch($left)
        {
            foreach ($left as $n=>$pattern) {
                if ($this->name == $pattern->name) {
                    return new SingleMatch($n, $pattern);
                }
            }
            return new SingleMatch(null, null);
        }

        /** @return string */
        public function name()
        {
            return $this->long ?: $this->short;
        }

        /** @return string */
        public function dump()
        {
            return "Option(".dump_scalar($this->short).", ".dump_scalar($this->long).", ".dump_scalar($this->argcount).", ".dump_scalar($this->value).")";
        }
    }

    class Required extends BranchPattern
    {
        /**
         * @param Pattern[] $left
         * @param Pattern[] $collected
         */
        public function match($left, $collected=null)
        {
            if (!$collected) {
                $collected = array();
            }
            
            $l = $left;
            $c = $collected;

            foreach ($this->children as $pattern) {
                list ($matched, $l, $c) = $pattern->match($l, $c);
                if (!$matched) {
                    return array(false, $left, $collected);
                }
            }
            
            return array(true, $l, $c);
        }
    }

    class Optional extends BranchPattern
    {
        /**
         * @param Pattern[] $left
         * @param Pattern[] $collected
         */
        public function match($left, $collected=null)
        {
            if (!$collected) {
                $collected = array();
            }
            
            foreach ($this->children as $pattern) {
                list($m, $left, $collected) = $pattern->match($left, $collected);
            }
            
            return array(true, $left, $collected);
        }
    }

    /**
     * Marker/placeholder for [options] shortcut.
     */
    class OptionsShortcut extends Optional
    {
    }

    class OneOrMore extends BranchPattern
    {
        /**
         * @param Pattern[] $left
         * @param Pattern[] $collected
         */
        public function match($left, $collected=null)
        {
            if (count($this->children) != 1) {
                throw new \UnexpectedValueException();
            }
            if (!$collected) {
                $collected = array();
            }
            
            $l = $left;
            $c = $collected;
            
            $lnew = array();
            $matched = true;
            $times = 0;
            
            while ($matched) {
                # could it be that something didn't match but changed l or c?
                list ($matched, $l, $c) = $this->children[0]->match($l, $c);
                if ($matched) $times += 1;
                if ($lnew == $l) {
                    break;
                }
                $lnew = $l;
            }
            
            if ($times >= 1) {
                return array(true, $l, $c);
            } else {
                return array(false, $left, $collected);
            }
        }
    }

    class Either extends BranchPattern
    {
        /**
         * @param Pattern[] $left
         * @param Pattern[] $collected
         */
        public function match($left, $collected=null)
        {
            if (!$collected) {
                $collected = array();
            }

            $outcomes = array();
            foreach ($this->children as $pattern) {
                list ($matched, $dump1, $dump2) = $outcome = $pattern->match($left, $collected);
                if ($matched) {
                    $outcomes[] = $outcome;
                }
            }
            if ($outcomes) {
                // return min(outcomes, key=lambda outcome: len(outcome[1]))
                $min = null;
                $ret = null;
                foreach ($outcomes as $o) {
                    $cnt = count($o[1]);
                    if ($min === null || $cnt < $min) {
                       $min = $cnt;
                       $ret = $o;
                    }
                }
                return $ret;
            }
            else {
                return array(false, $left, $collected);
            }
        }
    }

    class Tokens extends \ArrayIterator
    {
        /** @var string */
        public $error;
        
        /**
         * @param array|string $source
         * @param string $error Class name of error exception
         */
        public function __construct($source, $error='ExitException')
        {
            if (!is_array($source)) {
                $source = trim($source);
                if ($source) {
                    $source = preg_split('/\s+/', $source);
                } else {
                    $source = array();
                }
            }
            
            parent::__construct($source);
                    
            $this->error = $error; 
        }
        
        /**
         * @param string $source
         * @return self
         */
        public static function fromPattern($source)
        {
            $source = preg_replace('@([\[\]\(\)\|]|\.\.\.)@', ' $1 ', $source);
            $source = preg_split('@\s+|(\S*<.*?'.'>)@', $source, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            
            return new static($source, 'LanguageError');
        }

        /**
         * @return string
         */
        function move()
        {
            $item = $this->current();
            $this->next();
            return $item;
        }

        /**
         * @return string[]
         */
        function left()
        {
            $left = array();
            while (($token = $this->move()) !== null) {
                $left[] = $token;
            }
            return $left;
        }

        /**
         * @param string $message
         */
        function raiseException($message)
        {
            $class = __NAMESPACE__.'\\'.$this->error;
            throw new $class($message);
        }
    }

    /**
     * long ::= '--' chars [ ( ' ' | '=' ) chars ] ;
     *
     * @return Option[]
     */
    function parse_long(Tokens $tokens, \ArrayIterator $options)
    {
        $token = $tokens->move();
        $exploded = explode('=', $token, 2);
        if (count($exploded) == 2) {
            $long = $exploded[0];
            $eq = '=';
            $value = $exploded[1];
        }
        else {
            $long = $token;
            $eq = null;
            $value = null;
        }

        if (strpos($long, '--') !== 0) {
            throw new \UnexpectedValueException("Expected long option, found '$long'");
        }
        
        $value = (!$eq && !$value) ? null : $value;

        $filter = function($o) use ($long) { return $o->long && $o->long == $long; };
        $similar = array_filter($options, $filter, true);
        if ('ExitException' == $tokens->error && !$similar) {
            $filter = function($o) use ($long) { return $o->long && strpos($o->long, $long)===0; };
            $similar = array_filter($options, $filter, true);
        }

        if (count($similar) > 1) {
            // might be simply specified ambiguously 2+ times?
            $tokens->raiseException("$long is not a unique prefix: ".
                implode(', ', array_map(function($o) { return $o->long; }, $similar)));
        }
        elseif (count($similar) < 1) {
            $argcount = $eq == '=' ? 1 : 0;
            $o = new Option(null, $long, $argcount);
            $options[] = $o;
            if ($tokens->error == 'ExitException') {
                $o = new Option(null, $long, $argcount, $argcount ? $value : true);
            }
        }
        else {
            $o = new Option($similar[0]->short, $similar[0]->long, $similar[0]->argcount, $similar[0]->value);
            if ($o->argcount == 0) {
                if ($value !== null) {
                    $tokens->raiseException("{$o->long} must not have an argument");
                }
            }
            else {
                if ($value === null) {
                    if ($tokens->current() === null || $tokens->current() == "--") {
                        $tokens->raiseException("{$o->long} requires argument");
                    }
                    $value = $tokens->move();
                }
            }
            if ($tokens->error == 'ExitException') {
                $o->value = $value !== null ? $value : true;
            }
        }
        return array($o);
    }

    /**
     * shorts ::= '-' ( chars )* [ [ ' ' ] chars ] ;
     *
     * @return Option[]
     */
    function parse_shorts(Tokens $tokens, \ArrayIterator $options)
    {
        $token = $tokens->move();

        if (strpos($token, '-') !== 0 || strpos($token, '--') === 0) {
            throw new \UnexpectedValueException("short token '$token' does not start with '-' or '--'");
        }

        $left = ltrim($token, '-');
        $parsed = array();
        while ($left != '') {
            $short = '-'.$left[0];
            $left = substr($left, 1);
            $similar = array();
            foreach ($options as $o) {
                if ($o->short == $short) {
                    $similar[] = $o;
                }
            }

            $similarCnt = count($similar);
            if ($similarCnt > 1) {
                $tokens->raiseException("$short is specified ambiguously $similarCnt times");
            }
            elseif ($similarCnt < 1) {
                $o = new Option($short, null, 0);
                $options[] = $o;
                if ($tokens->error == 'ExitException') {
                    $o = new Option($short, null, 0, true);
                }
            }
            else {
                $o = new Option($short, $similar[0]->long, $similar[0]->argcount, $similar[0]->value);
                $value = null;
                if ($o->argcount != 0) {
                    if ($left == '') {
                        if ($tokens->current() === null || $tokens->current() == '--') {
                            $tokens->raiseException("$short requires argument");
                        }
                        $value = $tokens->move();
                    }
                    else {
                        $value = $left;
                        $left = '';
                    }
                }
                if ($tokens->error == 'ExitException') {
                    $o->value = $value !== null ? $value : true;
                }
            }
            $parsed[] = $o;
        }

        return $parsed;
    }

    /**
     * @param string $source
     * @return Required
     */
    function parse_pattern($source, \ArrayIterator $options)
    {
        $tokens = Tokens::fromPattern($source);
        $result = parse_expr($tokens, $options);
        if ($tokens->current() != null) {
            $tokens->raiseException('unexpected ending: '.implode(' ', $tokens->left()));
        }
        return new Required($result);
    }

    /**
     * expr ::= seq ( '|' seq )* ;
     *
     * @return Either|Pattern[]
     */
    function parse_expr(Tokens $tokens, \ArrayIterator $options)
    {
        $seq = parse_seq($tokens, $options);
        if ($tokens->current() != '|') {
            return $seq;
        }
        
        $result = null;
        if (count($seq) > 1) {
            $result = array(new Required($seq));
        } else {
            $result = $seq;
        }
        
        while ($tokens->current() == '|') {
            $tokens->move();
            $seq = parse_seq($tokens, $options);
            if (count($seq) > 1) {
                $result[] = new Required($seq);
            } else {
                $result = array_merge($result, $seq);
            }
        }

        if (count($result) > 1) {
            return new Either($result);
        } else {
            return $result;
        }
    }

    /**
     * seq ::= ( atom [ '...' ] )* ;
     *
     * @return Pattern[]
     */
    function parse_seq(Tokens $tokens, \ArrayIterator $options)
    {
        $result = array();
        $not = array(null, '', ']', ')', '|');
        while (!in_array($tokens->current(), $not, true)) {
            $atom = parse_atom($tokens, $options);
            if ($tokens->current() == '...') {
                $atom = array(new OneOrMore($atom));
                $tokens->move();
            }
            if ($atom) {
                $result = array_merge($result, $atom);
            }
        }
        return $result;
    }

    /**
     * atom ::= '(' expr ')' | '[' expr ']' | 'options'
     *       | long | shorts | argument | command ;
     * 
     * @return Pattern[]
     */
    function parse_atom(Tokens $tokens, \ArrayIterator $options)
    {
        $token = $tokens->current();
        $result = array();
        
        if ($token == '(' || $token == '[') {
            $tokens->move();
            
            static $index;
            if (!$index) {
                $index = array('('=>array(')', __NAMESPACE__.'\Required'), '['=>array(']', __NAMESPACE__.'\Optional'));
            }
            list ($matching, $pattern) = $index[$token];
            
            $result = new $pattern(parse_expr($tokens, $options));
            if ($tokens->move() != $matching) {
                $tokens->raiseException("Unmatched '$token'");
            }
            
            return array($result);
        }
        elseif ($token == 'options') {
            $tokens->move();
            return array(new OptionsShortcut);
        }
        elseif (strpos($token, '--') === 0 && $token != '--') {
            return parse_long($tokens, $options);
        }
        elseif (strpos($token, '-') === 0 && $token != '-' && $token != '--') {
            return parse_shorts($tokens, $options);
        }
        elseif (strpos($token, '<') === 0 && ends_with($token, '>') || is_upper($token)) {
            return array(new Argument($tokens->move()));
        }
        else {
            return array(new Command($tokens->move()));
        }
    }

    /**
     * Parse command-line argument vector.
     * 
     * If options_first:
     *     argv ::= [ long | shorts ]* [ argument ]* [ '--' [ argument ]* ] ;
     * else:
     *     argv ::= [ long | shorts | argument ]* [ '--' [ argument ]* ] ;
     * 
     * @param bool $optionsFirst
     * @return Pattern[]
     */
    function parse_argv(Tokens $tokens, \ArrayIterator $options, $optionsFirst=false)
    {
        $parsed = array();
        
        while ($tokens->current() !== null) {
            if ($tokens->current() == '--') {
                while ($tokens->current() !== null) {
                    $parsed[] = new Argument(null, $tokens->move());
                }
                return $parsed;
            }
            elseif (strpos($tokens->current(), '--')===0) {
                $parsed = array_merge($parsed, parse_long($tokens, $options));
            }
            elseif (strpos($tokens->current(), '-')===0 && $tokens->current() != '-') {
                $parsed = array_merge($parsed, parse_shorts($tokens, $options));
            }
            elseif ($optionsFirst) {
                return array_merge($parsed, array_map(function($v) { return new Argument(null, $v); }, $tokens->left()));
            }
            else {
                $parsed[] = new Argument(null, $tokens->move());
            }
        }
        return $parsed;
    }

    /**
     * @param string $doc
     * @return \ArrayIterator
     */
    function parse_defaults($doc)
    {   
        $defaults = array();
        foreach (parse_section('options:', $doc) as $s) {
            # FIXME corner case "bla: options: --foo"
            list (, $s) = explode(':', $s, 2);
            $splitTmp = array_slice(preg_split("@\n[ \t]*(-\S+?)@", "\n".$s, null, PREG_SPLIT_DELIM_CAPTURE), 1);
            $split = array();
            for ($cnt = count($splitTmp), $i=0; $i < $cnt; $i+=2) {
                $split[] = $splitTmp[$i] . (isset($splitTmp[$i+1]) ? $splitTmp[$i+1] : '');
            }
            $options = array();
            foreach ($split as $s) {
                if (strpos($s, '-') === 0) {
                    $options[] = Option::parse($s);
                }
            }
            $defaults = array_merge($defaults, $options);
        }
        
        return new \ArrayIterator($defaults);
    }
    
    /**
     * @param string $name
     * @param string $source
     * @return string[]
     */
    function parse_section($name, $source)
    {
        $ret = array();
        if (preg_match_all('@^([^\n]*'.$name.'[^\n]*\n?(?:[ \t].*?(?:\n|$))*)@im', 
                           $source, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $ret[] = trim($match[0]);
            }
        }
        return $ret;
    }

    /**
     * @param string $section
     * @return string
     */
    function formal_usage($section)
    {
        list (, $section) = explode(':', $section, 2);  # drop "usage:"
        $pu = preg_split('/\s+/', trim($section));
        
        $ret = array();
        foreach (array_slice($pu, 1) as $s) {
            if ($s == $pu[0]) {
                $ret[] = ') | (';
            } else {
                $ret[] = $s; 
            }
        }
        
        return '( '.implode(' ', $ret).' )';
    }

    /**
     * @param bool $help
     * @param ?string $version
     * @param Pattern[] $argv
     * @param string $doc
     */
    function extras($help, $version, $argv, $doc)
    {
        $ofound = false;
        $vfound = false;
        foreach ($argv as $o) {
            if ($o->value && ($o->name == '-h' || $o->name == '--help')) {
                $ofound = true; 
            }
            if ($o->value && $o->name == '--version') {
                $vfound = true;
            }
        }
        if ($help && $ofound) {
            ExitException::$usage = null;
            throw new ExitException($doc, 0);
        }
        if ($version && $vfound) {
            ExitException::$usage = null;
            throw new ExitException($version, 0);
        }
    }

    class Handler
    {
        /** @var bool */
        public $exit = true;

        /** @var bool */
        public $exitFullUsage = false;

        /** @var bool */
        public $help = true;

        /** @var bool */
        public $optionsFirst = false;

        /** @var ?string */
        public $version;

        public function __construct($options=array())
        {
            foreach ($options as $k=>$v) {
                $this->$k = $v;
            }
        }
        
        /**
         * @param string $doc
         * @param array $argv
         * @return Response
         */
        function handle($doc, $argv=null)
        {
            try {
                if ($argv === null && isset($_SERVER['argv'])) {
                    $argv = array_slice($_SERVER['argv'], 1);
                }
                
                $usageSections = parse_section('usage:', $doc);
                if (count($usageSections) == 0) {
                    throw new LanguageError('"usage:" (case-insensitive) not found.');
                } elseif (count($usageSections) > 1) {
                    throw new LanguageError('More than one "usage:" (case-insensitive).');
                }
                $usage = $usageSections[0];
                
                // temp fix until python port provides solution
                ExitException::$usage = !$this->exitFullUsage ? $usage : $doc;

                $options = parse_defaults($doc);
                
                $formalUse = formal_usage($usage);
                $pattern = parse_pattern($formalUse, $options);
                
                $argv = parse_argv(new Tokens($argv), $options, $this->optionsFirst);
                
                $patternOptions = $pattern->flat('Option');
                foreach ($pattern->flat('OptionsShortcut') as $optionsShortcut) {
                    $docOptions = parse_defaults($doc);
                    $optionsShortcut->children = array_diff((array)$docOptions, $patternOptions);
                }

                extras($this->help, $this->version, $argv, $doc);

                list($matched, $left, $collected) = $pattern->fix()->match($argv);
                if ($matched && !$left) {
                    $return = array();
                    foreach (array_merge($pattern->flat(), $collected) as $a) {
                        $name = $a->name;
                        if ($name) {
                            $return[$name] = $a->value;
                        }
                    }
                    return new Response($return);
                }
                throw new ExitException();
            }
            catch (ExitException $ex) {
                $this->handleExit($ex);
                return new Response(array(), $ex->status, $ex->getMessage());
            }
        }
        
        function handleExit(ExitException $ex)
        {
            if ($this->exit) {
                echo $ex->getMessage().PHP_EOL;
                exit($ex->status);
            }
        }
    }

    class Response implements \ArrayAccess, \IteratorAggregate
    {
        /** @var int */
        public $status;

        /** @var string */
        public $output;

        /** @var array */
        public $args;
        
        /**
         * @param array $args
         * @param int $status
         * @param string $output
         */
        public function __construct(array $args, $status=0, $output='')
        {
            $this->args = $args;
            $this->status = $status;
            $this->output = $output;
        }

        public function __get($name)
        {
            if ($name == 'success') {
                return $this->status === 0;
            } else {
                throw new \BadMethodCallException("Unknown property $name");
            }
        }

        public function offsetExists($offset)
        {
            return isset($this->args[$offset]);
        }

        public function offsetGet($offset)
        {
            return $this->args[$offset];
        }

        public function offsetSet($offset, $value)
        {
            $this->args[$offset] = $value;
        }

        public function offsetUnset($offset)
        {
            unset($this->args[$offset]);
        }

        public function getIterator()
        {
            return new \ArrayIterator($this->args);
        }
    }

    class SingleMatch
    {
        /** @var ?int */
        public $pos;

        /** @var Pattern */
        public $pattern;

        /**
         * @param ?int $pos
         * @param Pattern $pattern
         */
        public function __construct($pos, Pattern $pattern=null)
        {
            $this->pos = $pos;
            $this->pattern = $pattern;
        }

        public function toArray() { return array($this->pos, $this->pattern); }
    }
}
