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
		if (limitTo) {
			content = content.substring(0, limitTo);
		}
		return content;
	};
}]).filter('array', ['AppUtil', function(AppUtil) {
	return function(num){
		return AppUtil.createArray(num);
	};
}]).
filter('formatPrice', ['$rootScope', function($rootScope){

	return function(price) {
		if (price) {
			price = parseFloat(price);
			return parseFloat(price) <= 0 ? "免费" : "¥" + price.toFixed(2);
		}
		return "";
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
	return function(time) {
		return new Date(time).Format("yyyy-MM-dd");
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
			return src;
		}
		return app.viewFloder  + "img/avatar.png";
	}
}]);