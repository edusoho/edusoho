<?php
/**
 * 是将日期转化为一个对象去操作
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindGeneralDate.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package utility
 */
class WindGeneralDate {

	/**
	 * 填充展示
	 * 
	 * @var int 
	 */
	const FILL = 0;

	/**
	 * 数字展示
	 * 
	 * @var int 
	 */
	const DIGIT = 1;

	/**
	 * 文本展示
	 * 
	 * @var int 
	 */
	const TEXT = 2;

	/**
	 * 默认格式化
	 * 
	 * @var string 
	 */
	const DEFAULT_FORMAT = 'Y-m-d H:i:s';

	/**
	 * unix时间戳
	 * 
	 * @var int 
	 */
	private $time = 0;

	/**
	 * 根据输入的日期格式转化为时间戳进行属性time初始化
	 * 
	 * mktime函数，在只有输入一个年份的时候，就会默认转化为上一年的最后一天，输入一个月份并且缺省输入day的时候，
	 * 会转化为上个月的最后一天。所以这种情况需要注意。
	 * 如果该构造函数没有参数传入的时候，得到的日期不是期望的当前日期，而是上两年的11月的30日
	 * 
	 * 如果月份为空：如果年份为空，则取当前月份；否则取1
	 * 如果日期为空：如果年份为空，则取当前日期，否则取1
	 * 如果小时为空：如果年份为空，则取当前小时
	 * 如果分为空：如果年份为空，则取当前分
	 * 如果秒为空：如果年份为空，则取当前秒
	 * 如果年份为空：取当前年份
	 * 
	 * @param int $year     年,默认为null，获取当前年
	 * @param int $month    月,默认为null获取当前月
	 * @param int $day      日,默认为null获取当前日期
	 * @param int $hours    小时,默认为null获取当前小时
	 * @param int $minutes  分,默认为null获取当前分钟
	 * @param int $second   秒,默认为null获取当前秒
	 * @return void
	 */
	public function __construct($year = null, $month = null, $day = null, $hours = null, $minutes = null, $second = null) {
		$time = time();
		!$month && ((!$year) ? $month = date('m', $time) : $month = 1);
		!$day && ((!$year) ? $day = date('d', $time) : $day = 1);
		!$hours && !$year && $hours = date('H', $time);
		!$minutes && !$year && $minutes = date('i', $time);
		!$second && !$year && $second = date('s', $time);
		!$year && $year = date('Y', $time);
		$this->time = mktime($hours, $minutes, $second, $month, $day, $year);
	}

	/**
	 * 获取当前时间所在月的天数
	 * 
	 * @return string 
	 */
	public function getDaysInMonth() {
		return date('t', $this->time);
	}

	/**
	 * 获取当前时间所在年的天数 
	 * 
	 * @return int 如果是闰年返回366否则返回365
	 */
	public function getDaysInYear() {
		return $this->isLeapYear() ? 366 : 365;
	}

	/**
	 * 所表示当前日期是该年中的第几天。
	 * 
	 * @return int 返回时该年中的第几天
	 */
	public function getDayOfYear() {
		return date('z', $this->time) + 1;
	}

	/**
	 * 表示当前日期为该月中的第几天。 
	 * 
	 * @return int 
	 */
	public function getDayOfMonth() {
		return date('j', $this->time);
	}

	/**
	 * 表示当前日期是该星期中的第几天。
	 * 
	 * @return int 
	 */
	public function getDayOfWeek() {
		return date('w', $this->time) + 1;
	}

	/**
	 * 判断当前日期所在年的第几周 
	 * 
	 * @return int
	 */
	public function getWeekOfYear() {
		return date('W', $this->time);
	}

	/**
	 * 获取当前日期的年份
	 * 
	 * @param boolean $format 是否返回四位格式的年份或是两位格式的年份，默认为true则以Y返回四位数
	 * @return string
	 */
	public function getYear($format = true) {
		return date($format ? 'Y' : 'y', $this->time);
	}

	/**
	 * 获当前日期的取月份
	 * 
	 * @param int $display 显示类型，默认为0，则显示两位的月份
	 * @return string
	 */
	public function getMonth($display = self::FILL) {
		if (self::FILL == $display) {
			return date('m', $this->time);
		} elseif (self::DIGIT == $display) {
			return date('n', $this->time);
		} elseif (self::TEXT == $display) {
			return date('M', $this->time);
		}
		return date('n', $this->time);
	}

	/**
	 * 获取当前日期的天数
	 * 
	 * @param string $display 显示类型，默认为0，显示两位的日期
	 * @return string 
	 */
	public function getDay($display = self::FILL) {
		if (self::FILL == $display) {
			return date('d', $this->time);
		} elseif (self::DIGIT == $display) {
			return date('j', $this->time);
		} elseif (self::TEXT == $display) {
			return date('jS', $this->time);
		}
		return date('j', $this->time);
	}

	/**
	 * 获取当前日期的星期
	 * 
	 * @param string $display 显示类型，默认为0，返回数字表示的星期中的第几天
	 * @return string
	 */
	public function getWeek($display = self::FILL) {
		if (self::FILL == $display || self::DIGIT == $display) {
			return date('w', $this->time);
		} elseif (self::TEXT == $display) {
			return date('D', $this->time);
		}
		return date('N', $this->time);
	}

	/**
	 * 获取当前日期的12小时制时间
	 * 
	 * @param string $display 显示类型，默认为0，显示两位的小时
	 * @return string
	 */
	public function get12Hours($display = self::FILL) {
		if (self::FILL == $display) {
			return date('h', $this->time);
		} elseif (self::DIGIT == $display) {
			return date('g', $this->time);
			;
		}
		return date('h', $this->time);
	}

	/**
	 * 获取当前日期的24小时制时间
	 * 
	 * @param string $display 显示类型，默认为0，显示两位的小时
	 * @return string
	 */
	public function get24Hours($display = self::FILL) {
		if (self::FILL == $display) {
			return date('H', $this->time);
		} elseif (self::DIGIT == $display) {
			return date('G', $this->time);
			;
		}
		return date('H', $this->time);
	}

	/**
	 * 获取当前日期的分钟
	 * 
	 * @return string
	 */
	public function getMinutes() {
		return date('i', $this->time);
	}

	/**
	 * 获取当前日期的秒数
	 * 
	 * @return string
	 */
	public function getSeconds() {
		return date('s', $this->time);
	}

	/**
	 * 获取当前日期的本地时区
	 * 
	 * @return string
	 */
	public function getLocalTimeZone() {
		return date('T', $this->time);
	}

	/**
	 * 重新设置当前日期与时间
	 * 
	 * @param string  $time 时间戳
	 * @return void  
	 */
	public function setTime($time) {
		if (is_int($time) || (is_string($time) && ($time = strtotime($time)))) {
			$this->time = $time;
		}
	}

	/**
	 * 取得当前日期时间对象
	 * 
	 * @return WindGeneralDate
	 */
	public function getNow() {
		$date = getdate($this->time);
		return new self($date["year"], $date["mon"], $date["mday"], $date["hours"], $date["minutes"], $date["seconds"]);
	}

	/**
	 * 对象转化为字符串，魔术方法
	 * 
	 * @return string 
	 */
	public function __toString() {
		return $this->toString();
	}

	/**
	 * 格式化时间输出
	 * 
	 * @param string $format 需要输出的格式,默认为null，则采用格式Y-m-d H:i:s
	 * @return string 
	 */
	public function toString($format = null) {
		return date($format ? $format : self::DEFAULT_FORMAT, $this->time);
	}

	/**
	 * 判断是否是闰年
	 * 
	 * @return int  返回1或是0
	 */
	public function isLeapYear() {
		return date('L', $this->time);
	}
}