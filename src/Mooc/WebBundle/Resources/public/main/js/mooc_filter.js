app.filter('coverCourseStartTime', ['$rootScope', function($rootScope){

	return function(course) {
		if (! course || "periodic" != course.type) {
			return "<span class='start'>开课中</span>";
		}

	  	var currentDate = new Date(course.now).getTime();
	  	var endData = new Date(course.endTime).getTime();

	  	if (currentDate > endData) {
	  		return "<span class='end'>已结束</span>";
	  	}

	  	var startData = new Date(course.startTime).getTime();
	  	if (currentDate >= startData) {
	  		return "<span class='start'>开课中</span>";
	  	}

	  	return "<span class='enroll'>报名中</span>";

	}
}])
.filter('coverCourseSudentNum', ['$rootScope', function($rootScope){

	return function(course) {
		if ("periodic" != course.type) {
			return course.studentNum + "人在学";
		}

	  	var currentDate = new Date(course.now).getTime();
	  	var endData = new Date(course.endTime).getTime();

	  	if (currentDate > endData) {
	  		return "";
	  	}

	  	var startData = new Date(course.startTime).getTime();
	  	if (currentDate >= startData) {
	  		return course.studentNum + "人在学";
	  	}

	  	return course.studentNum + "人报名";

	}
}])
.filter('coverMoocLearnTime', ['$rootScope', function($rootScope){
	return function(course) {
		var startSpaceTime = new Date(course.now).getTime() - new Date(course.startTime).getTime();
		if (new Date(course.now).getTime() - new Date(course.endTime).getTime() >= 0) {
			return '<span class="discount-type discount end">已结束</span>';
		}
		if (startSpaceTime >= 0) {
			var totalSpaceTime = new Date(course.endTime).getTime() - new Date(course.startTime).getTime();
			var startLearnStr = Math.ceil(startSpaceTime / 604800000) + "/" + Math.ceil(totalSpaceTime / 604800000);
			return '已进行至:<span class="discount-type discount">' + startLearnStr + '</span>';
		}

		var lasterStartTime = new Date(course.startTime) - new Date(course.now).getTime();
		var currentDay = parseInt(lasterStartTime / (60000*60) -1) //减去1小时
		if(currentDay >= 24*30){
			var nd = new Date();
			nd.setTime(lasterStartTime);
            currentDay = nd.Format("yyyy-MM-dd");
        }else if(currentDay >= 24){
            currentDay = parseInt(currentDay / 24);
        }else{
            currentDay = 0;
        }

        return '距离开课:<span class="discount-type discount">' + currentDay + '</span>天';
	}
}])
.filter('coverMoocTime', ['$rootScope', function($rootScope){
	return function(time) {
		return new Date(time).Format("yyyy-MM-dd");
	}
}])
.filter('coverIncludePath', function() {
	return function(path) {
		var realPath = app.viewFloderMap[path];
		if (realPath) {
			return realPath;
		}
		return app.viewFloder + path;
	}
})
.filter('coverMoocLearnBtn', function() {
	return function(course, member) {
		if ("periodic" != course.type) {
			return member ? "继续学习" : "立即加入";
		}

	  	var currentDate = new Date(course.now).getTime();
	  	var endData = new Date(course.endTime).getTime();

	  	if (currentDate > endData) {
	  		return "课程已结束";
	  	}

	  	var startData = new Date(course.startTime).getTime();
	  	if (currentDate >= startData) {
	  		return member ? "继续学习" : "加入学习";
	  	}

	  	return member ? "等待开课" : "提交报名";
	}
})
.filter('coverMoocLearnBtnColor', function() {
	return function(course) {
		if ("periodic" != course.type) {
			return "btn-green";
		}

	  	var currentDate = new Date(course.now).getTime();
	  	var endData = new Date(course.endTime).getTime();

	  	if (currentDate > endData) {
	  		return "btn-gray-normal";
	  	}

	  	var startData = new Date(course.startTime).getTime();
	  	if (currentDate >= startData) {
	  		return "btn-green";
	  	}

	  	return "btn-yellow";

	}
});