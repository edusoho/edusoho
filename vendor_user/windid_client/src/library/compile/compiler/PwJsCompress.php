<?php
Wind::import("LIB:compile.AbstractPwCompiler");
class PwJsCompress extends AbstractPwCompiler {
	
	/*
	 * (non-PHPdoc) @see AbstractPwCompiler::doCompile()
	 */
	public function doCompile() {
		$JS_DEV_PATH = Wind::getRealDir('PUBLIC:res.js.dev');
		$JS_BUILD_PATH = Wind::getRealDir('PUBLIC:res.js.build');
		
		Wind::import('Wind:utility.WindFolder');
		$files = $this->_getFiles($JS_DEV_PATH);
		foreach ($files as $file) {
			$newfile = $JS_BUILD_PATH . substr($file, strlen($JS_DEV_PATH));
			WindFolder::mkRecur(dirname($newfile));
			if (substr($file, -3) != '.js') {
				if (!copy($file, $newfile)) {
					return new PwError('copy failed');
				}
				continue;
			}
			$content = WindFile::read($file);
			$compress = jscompress::pack($content);
			if (!WindFile::write($newfile, $compress))
				return new PwError('write failed');
		}
	}

	private function _getFiles($dir, $skipHiddenDirs = true) {
		if (!$handle = @opendir($dir)) return array();
		$files = array();
		while (false !== ($file = @readdir($handle))) {
			if ('.' === $file || '..' === $file) continue;
			if ($skipHiddenDirs && substr($file, 0, 1) === '.') continue;
			if (is_dir($dir . '/' . $file)) {
				$files = array_merge($files, $this->_getFiles($dir . '/' . $file));
			} elseif (is_file($dir . '/' . $file)) {
				$files[] = $dir . '/' . $file;
			}
		}
		@closedir($handle);
		return $files;
	}
}
/*
 * @名称:JS代码压缩 @作者:风吟 @演示:无 @网站:http://demos.fengyin.name/
 * @博客:http://fengyin.name/ @更新:2009年9月22日 20:29:24 @版权:Copyright (c)
 * 风吟版权所有，本程序为开源程序(开放源代码)。 只要你遵守 MIT licence 协议.您就可以自由地传播和修改源码以及创作衍生作品.
 */
/**
 * jsmin.php - PHP implementation of Douglas Crockford's JSMin.
 *
 * This is pretty much a direct port of jsmin.c to PHP with just a few
 * PHP-specific performance tweaks. Also, whereas jsmin.c reads from stdin and
 * outputs to stdout, this library accepts a string as input and returns another
 * string as output.
 *
 * PHP 5 or higher is required.
 *
 * Permission is hereby granted to use this version of the library under the
 * same terms as jsmin.c, which has the following license:
 *
 * --
 * Copyright (c) 2002 Douglas Crockford (www.crockford.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to
 * do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all
 * copies or substantial portions of the Software.
 *
 * The Software shall be used for Good, not Evil.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * --
 *
 * @package JSMin
 * @author Ryan Grove <ryan@wonko.com>
 * @copyright 2002 Douglas Crockford <douglas@crockford.com> (jsmin.c)
 * @copyright 2008 Ryan Grove <ryan@wonko.com> (PHP port)
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @version 1.1.1 (2008-03-02)
 * @link http://code.google.com/p/jsmin-php/
 *      
 *      
 */
class jscompress {
	const ord_lf = 10;
	const ord_space = 32;
	protected $a = '';
	protected $b = '';
	protected $input = '';
	protected $inputindex = 0;
	protected $inputlength = 0;
	protected $lookahead = null;
	protected $output = '';

	public static function pack($js) {
		$jsmin = new jscompress($js);
		return $jsmin->min();
	}

	public function __construct($input) {
		$this->input = str_replace("\r\n", "\n", $input);
		$this->inputlength = strlen($this->input);
	}

	protected function action($d) {
		switch ($d) {
			case 1:
				$this->output .= $this->a;
			case 2:
				$this->a = $this->b;
				if ($this->a === "'" || $this->a === '"') {
					for (;;) {
						$this->output .= $this->a;
						$this->a = $this->get();
						if ($this->a === $this->b) {
							break;
						}
						if (ord($this->a) <= self::ord_lf) {
							throw new jscompressexception('unterminated string literal.');
						}
						if ($this->a === '\\') {
							$this->output .= $this->a;
							$this->a = $this->get();
						}
					}
				}
			case 3:
				$this->b = $this->next();
				if ($this->b === '/' && ($this->a === '(' || $this->a === ',' || $this->a === '=' || $this->a === ':' || $this->a === '[' || $this->a === '!' || $this->a === '&' || $this->a === '|' || $this->a === '?')) {
					$this->output .= $this->a . $this->b;
					for (;;) {
						$this->a = $this->get();
						if ($this->a === '/') {
							break;
						} elseif ($this->a === '\\') {
							$this->output .= $this->a;
							$this->a = $this->get();
						} elseif (ord($this->a) <= self::ord_lf) {
							throw new jscompressexception(
								'unterminated regular expression ' . 'literal.');
						}
						$this->output .= $this->a;
					}
					$this->b = $this->next();
				}
		}
	}

	protected function get() {
		$c = $this->lookahead;
		$this->lookahead = null;
		if ($c === null) {
			if ($this->inputindex < $this->inputlength) {
				$c = $this->input[$this->inputindex];
				$this->inputindex += 1;
			} else {
				$c = null;
			}
		}
		if ($c === "\r") {
			return "\n";
		}
		if ($c === null || $c === "\n" || ord($c) >= self::ord_space) {
			return $c;
		}
		return ' ';
	}

	protected function isalphanum($c) {
		return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
	}

	protected function min() {
		$this->a = "\n";
		$this->action(3);
		while ($this->a !== null) {
			switch ($this->a) {
				case ' ':
					if ($this->isalphanum($this->b)) {
						$this->action(1);
					} else {
						$this->action(2);
					}
					break;
				
				case "\n":
					switch ($this->b) {
						case '{':
						case '[':
						case '(':
						case '+':
						case '-':
							$this->action(1);
							break;
						
						case ' ':
							$this->action(3);
							break;
						
						default:
							if ($this->isalphanum($this->b)) {
								$this->action(1);
							} else {
								$this->action(2);
							}
					}
					break;
				default:
					switch ($this->b) {
						case ' ':
							if ($this->isalphanum($this->a)) {
								$this->action(1);
								break;
							}
							$this->action(3);
							break;
						case "\n":
							switch ($this->a) {
								case '}':
								case ']':
								case ')':
								case '+':
								case '-':
								case '"':
								case "'":
									$this->action(1);
									break;
								
								default:
									if ($this->isalphanum($this->a)) {
										$this->action(1);
									} else {
										$this->action(3);
									}
							}
							break;
						
						default:
							$this->action(1);
							break;
					}
			}
		}
		
		return $this->output;
	}

	protected function next() {
		$c = $this->get();
		if ($c === '/') {
			switch ($this->peek()) {
				case '/':
					for (;;) {
						$c = $this->get();
						
						if (ord($c) <= self::ord_lf) {
							return $c;
						}
					}
				case '*':
					$this->get();
					for (;;) {
						switch ($this->get()) {
							case '*':
								if ($this->peek() === '/') {
									$this->get();
									return ' ';
								}
								break;
							
							case null:
								throw new jscompressexception('unterminated comment.');
						}
					}
				default:
					return $c;
			}
		}
		return $c;
	}

	protected function peek() {
		$this->lookahead = $this->get();
		return $this->lookahead;
	}
}
class jscompressexception extends exception {}

?>