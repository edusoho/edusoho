<?php

namespace Topxia\Common;

class TimeUtils{

	
	public static function getThisWeekStartTime(){
		return strtotime("+0 week Monday");
	} 

	public static function getThisWeekEndTime(){
		return strtotime("+1 week Monday");
	}

	public static function getLastWeekStartTime(){
		return strtotime("-1 week Monday");
	}

	public static function getLastWeekEndTime(){
		return strtotime("+0 week Monday");
	}

	public static function getNextWeekStartTime(){
		return strtotime("+1 week Monday");
	}

	public static function getNextWeekEndTime(){
		return strtotime("+2 week Monday");
	}

	public static function getThisMonthStartTime(){
		return strtotime("+0 month Monday");
	}

	public static function getThisMonthEndTime(){
		return strtotime("+1 month Monday");
	}

	public static function getLastMonthStartTime(){
		return strtotime("-1 month Monday");	
	}

	public static function getLastMonthEndTime(){
		return strtotime("+0 month Monday");
	}

	public static function getNextMonthStartTime(){
		return strtotime("+1 month Monday");
	}

	public static function getNextMonthEndTime(){
		return strtotime("+2 month Monday");
	}


}