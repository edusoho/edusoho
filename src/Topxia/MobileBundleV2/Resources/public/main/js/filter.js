Date.prototype.Format = function(fmt) 
{ //author: meizz 
  var o = { 
    "M+" : this.getMonth()+1,                 //月份 
    "d+" : this.getDate(),                    //日 
    "h+" : this.getHours(),                   //小时 
    "m+" : this.getMinutes(),                 //分 
    "s+" : this.getSeconds(),                 //秒 
    "q+" : Math.floor((this.getMonth()+3)/3), //季度 
    "S"  : this.getMilliseconds()             //毫秒 
  }; 
  if(/(y+)/.test(fmt)) 
    fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length)); 
  for(var k in o) 
    if(new RegExp("("+ k +")").test(fmt)) 
  fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length))); 
  return fmt; 
}

app.filter('blockStr', ['$rootScope', function($rootScope) {
	return function(content, limitTo){
		if (!content) {
			return "";
		}
		content = content.replace(/<[^>]+>/g, "");
		if (limitTo && limitTo < content.length) {
			content = content.substring(0, limitTo);
			content += "...";
		}
		return content;
	};
}]).filter('array', ['AppUtil', function(AppUtil) {
	return function(num){
		return AppUtil.createArray(num);
	};
}]).filter('classRoomSignFilter', function() {
	return function(signInfo){
		if (!signInfo) {
			return "签到";
		}

		if (signInfo.isSignedToday) {
			var day = signInfo.userSignStatistics.keepDays ? signInfo.userSignStatistics.keepDays : 1;
			return "连续" + day + "天";
		}

		return "签到";
	};
}).
filter('lessonLearnStatus', function(){
	return function(progress) {
		if (progress.progressValue == 0) {
			return "还没开始学习";
		}

		if (progress.progressValue == 100) {
			return "已完结";
		}

		return "最近学习:" + progress.lastLesson.title;
	};
}).
filter('lessonType', function() {

	return function(lesson) {
		if (lesson.type == "live") {
			var returnStr = "";
			var startTime = lesson.startTime * 1000;
			var endTime = lesson.endTime * 1000;
			var currentTime = new Date().getTime();

			if (startTime > currentTime) {
				var showDate = new Date();
				showDate.setTime(startTime);
				returnStr = showDate.Format("MM月dd号 hh:mm");;
			} else if (startTime <= currentTime && endTime >= currentTime) {
				returnStr = "<div class='ui-label' >直播中</div>";
			}else if (endTime < currentTime) {
				if (lesson.replayStatus == 'generated' ) {
					returnStr = "<div class='ui-label gray' >回放</div>";
				} else {
					returnStr = "<div class='ui-label gray' >结束</div>";
				}
			}
			return returnStr;
		}
		if (lesson.free == 1) {
			return "<div class='ui-label'>免费</div>";
		}
		return "";
	}
}).
filter('coverIncludePath', function() {
	return function(path) {
		return app.viewFloder + path;
	}
}).
filter('formatPrice', ['$rootScope', function($rootScope){

	return function(price) {
		if (price) {
			price = parseFloat(price);
			return price <= 0 ? "免费" : "¥" + price.toFixed(2);
		}
		return price;
	}
}]).
filter('formatCoinPrice', ['$rootScope', function($rootScope){

	return function(price, coinName) {
		if (price) {
			if (!coinName) {
				coinName = "";
			}
			price = parseFloat(price);
			return price <= 0 ? "免费" : price.toFixed(2) + coinName;
		}
		return price;
	}
}]).
filter('coverLearnProsser', ['$rootScope', function($rootScope){

	return function(course) {
		var lessonNum = course.lessonNum;
		var memberLearnedNum = course.memberLearnedNum;
		
		return {
			width : (memberLearnedNum / lessonNum) * 100 + "%"
		}
	}
}]).
filter('reviewProgress', function(){

	return function(progress, total) {
		if (total == 0) {
			return "0%";
		}
		return ( (progress / total) * 100 ).toFixed(0) + "%";
	}
}).
filter('formatChapterNumber', ['$rootScope', function($rootScope){

	return function(chapter) {
		if (chapter.type != "chapter" && chapter.type != "unit") {
			return "";
		}
		var number = chapter.number;
		return "第" + number + (chapter.type == "chapter" ?  "章" : "节");
	}
}]).
filter('coverLearnTime', ['$rootScope', function($rootScope){
	return function(date) {
		if (! date) {
			return "还没开始学习";
		}

	  var currentDates = new Date().getTime() - new Date(date).getTime(),
	        currentDay = parseInt(currentDates / (60000*60) -1) //减去1小时
	        if(currentDay >= 24*3){
	            currentDay = new Date(date).Format("yyyy-MM-dd");
	        }else if(currentDay >= 24){
	            currentDay = parseInt(currentDay / 24) + "天前";
	        }else if(currentDay == 0 ){
	            var currentD = parseInt(currentDates / 60000);
	            if(currentD >= 60){
	                currentDay = "1小时前";
	            }else{
	                currentDay = currentD + "分钟前";
	            }
	        }else{
	            currentDay = currentDay + "小时前";
	        }

	  return currentDay;
	}
}]).
filter('coverArticleTime', ['$rootScope', function($rootScope){
	return function(date) {
		if (! date) {
			return "";
		}

	  var currentDates = new Date().getTime() - new Date(date).getTime(),
	        currentDay = parseInt(currentDates / (60000*60) -1) //减去1小时
	        if(currentDay >= 24*3){
	            currentDay = new Date(date).Format("yyyy-MM-dd");
	        }else if(currentDay >= 24){
	            currentDay = parseInt(currentDay / 24) + "天前";
	        }else if(currentDay == 0 ){
	            var currentD = parseInt(currentDates / 60000);
	            if(currentD >= 60){
	                currentDay = "1小时前";
	            }else{
	                currentDay = currentD + "分钟前";
	            }
	        }else{
	            currentDay = currentDay + "小时前";
	        }

	  return currentDay;
	}
}]).
filter('coverDiscountTime', ['$rootScope', function($rootScope){
	return function(endTime) {
		return new Date(new Date(endTime) - new Date()).Format("d天h小时m分钟");
	}
}]).
filter('coverViewPath', ['$rootScope', function($rootScope){
	return function(path) {
		return app.viewFloder + path;
	}
}]).
filter('coverNoticeIcon', ['$rootScope', function($rootScope){
	return function(type) {
		return app.viewFloder + "img/course_notice.png";
	}
}]).
filter('coverGender', ['$rootScope', function($rootScope){

	return function(gender) {
		switch (gender) {
			case "male":
				return "男士";
			case "female":
				return "女士";
			default:
				return "保密";

		}
	}
}]).
filter('coverPic', ['$rootScope', function($rootScope){

	return function(src) {
		if (src) {
			return src;
		}
		return app.viewFloder  + "img/course_default.jpg";
	}
}]).
filter('coverDiscount', ['$rootScope', function($rootScope){

	return function(type, discount) {
		if (type == "free") {
			return "限免";
		}
		var discountNum = parseFloat(discount);
		return discountNum + "折";
	}
}]).
filter('coverAvatar', ['$rootScope', function($rootScope){

	return function(src) {
		if (src) {
			if (src.indexOf("http://") == -1) {
				src = app.host + src;
			}
			return src;
		}
		return app.viewFloder  + "img/avatar.png";
	}
}]).
filter('questionResultStatusColor', function() {

	return function(status) {
		if ("noAnswer" == status) {

		}

		return "wrong";
	}
}).
filter('questionResultStatusIcon', function() {

	return function(status) {
		if ("noAnswer" == status) {

		}

		return "icon-wrong";
	}
}).
filter('fillAnswer', function() {

	return function(answer, index) {
		if (!answer) {
			return "";
		}

		if (answer[index]) {
			return "selected";
		}
	}
}).
filter('coverUserRole', function(AppUtil){

	return function(roles) {
		if (AppUtil.inArray("ROLE_SUPER_ADMIN", roles) != -1) {
			return "超管";
		};

		if (AppUtil.inArray("ROLE_ADMIN", roles) != -1) {
			return "管理员";
		};

		if (AppUtil.inArray("ROLE_TEACHER", roles) != -1) {
			return "教师";
		};
		return "学生";
	}
}).
filter('isTeacherRole', function(AppUtil){

	return function(roles) {
		if (AppUtil.inArray("ROLE_TEACHER", roles) != -1) {
			return true;
		}

		if (AppUtil.inArray("teacher", roles) != -1) {
			return true;
		}
		return false;
	}
});