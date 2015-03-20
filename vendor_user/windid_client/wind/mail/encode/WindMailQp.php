<?php
Wind::import("WIND:mail.IWindMailEncoder");
/**
 * 完成邮件传输过程中Qp的编码和解码
 * 
 * QP(Quote-Printable) 方法，通常缩写为“Q”方法，其原理是把一个 8 bit 
 * 的字符用两个16进制数值表示，然后在前面加“=”。
 * 所以我们看到经过QP编码后的文件通常是这个样子：
 * =B3=C2=BF=A1=C7=E5=A3=AC=C4=FA=BA=C3=A3=A1。
 * @author Qiong Wu <papa0924@gmail.com> 2012-1-1
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package mail
 * @subpackage encode
 */
class WindMailQp implements IWindMailEncoder {

	/* (non-PHPdoc)
	 * @see IWindMailEncoder::decode()
	 */
	public function decode($string, $length, $linebreak) {}

	/* (non-PHPdoc)
	 * @see IWindMailEncoder::decodeHeader()
	 */
	public function decodeHeader($string, $charset, $length, $linebread) {}

	/**
	 * 用Base64方式编码邮件内容
	 *
	 * @param string $string
	 * @param int $line_max
	 * @param string $linebreak
	 */
	public function encode($string, $line_max, $linebreak) {
		$hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
		$lines = preg_split("/(?:\r\n|\r|\n)/", $string);
		$line_max = $line_max - strlen($linebreak);
		$escape = "=";
		$output = "";
		$cur_conv_line = "";
		$length = 0;
		$whitespace_pos = 0;
		$addtl_chars = 0;
		for ($j = 0; $j < count($lines); $j++) {
			$line = $lines[$j];
			$linlen = strlen($line);
			for ($i = 0; $i < $linlen; $i++) {
				$c = substr($line, $i, 1);
				$dec = ord($c);
				$length++;
				if ($dec == 32) {
					if (($i == ($linlen - 1))) {
						$c = "=20";
						$length += 2;
					}
					$addtl_chars = 0;
					$whitespace_pos = $i;
				} else if (($dec == 61) || ($dec < 32) || ($dec > 126)) {
					$h2 = floor($dec / 16);
					$h1 = floor($dec % 16);
					$c = $escape . $hex["$h2"] . $hex["$h1"];
					$length += 2;
					$addtl_chars += 2;
				}
				if ($length >= $line_max) {
					$cur_conv_line .= $c;
					$whitesp_diff = $i - $whitespace_pos + $addtl_chars;
					if (($i + $addtl_chars) > $whitesp_diff) {
						$output .= substr($cur_conv_line, 0, (strlen($cur_conv_line) - $whitesp_diff)) . $linebreak;
						$i = $i - $whitesp_diff + $addtl_chars;
					} else {
						$output .= $cur_conv_line . $linebreak;
					}
					$cur_conv_line = "";
					$length = 0;
					$whitespace_pos = 0;
				} else
					$cur_conv_line .= $c;
			}
			$length = 0;
			$whitespace_pos = 0;
			$output .= $cur_conv_line;
			if ($j <= count($lines) - 1) {
				$output .= $linebreak;
			}
			$cur_conv_line = "";
		}
		return trim($output);
	}

	/**
	 * 编码邮件头
	 *
	 * @param string $string
	 * @param string $charset
	 * @param int $length
	 * @param string $linebreak
	 */
	public function encodeHeader($string, $charset, $length, $linebreak) {
		$prefix = '=?' . $charset . '?Q?';
		$suffix = '?=';
		$length = $length - strlen($prefix) - strlen($suffix);
		$string = $this->encode($string, $length, $linebreak);
		return $prefix . strtr($string, array($linebreak => $suffix . $linebreak . " $prefix")) . $suffix;
	}
}
?>