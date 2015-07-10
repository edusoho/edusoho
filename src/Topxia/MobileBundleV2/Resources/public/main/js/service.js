var appService = angular.module('AppService', []);
appService.service('localStore', ['$rootScope', function($rootScope) {
	this.save = function(key, value){
		localStorage.setItem(key, value);
	}

	this.remove = function(key) {
		localStorage.removeItem(key);
	}

	this.get = function(key) {
		value = localStorage.getItem(key);
		return value ? value : null; 
	}

	this.clear = function() {
		localStorage.clear();
	}
}]).
service('CategoryService', ['httpService', function(httpService) {

	this.getCategorieTree = function(callback) {
		httpService.get({
			url : app.host + '/mapi_v2/Category/getCategorieTree',
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}
}]).
service('LessonService', ['httpService', function(httpService) {
	
	this.getLesson = function(callback) {
		httpService.simpleGet("/mapi_v2/Lesson/getLesson", arguments);
	}

	this.getCourseLessons = function(params, callback) {
		httpService.get({
			url : app.host + '/mapi_v2/Lesson/getCourseLessons',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}
}]).
service('OrderService', ['httpService', function(httpService) {

	this.payCourse = function(params, callback) {
		httpService.simplePost("/mapi_v2/Order/payCourse", arguments);
	}

	this.payVip = function(params, callback) {
		httpService.simplePost("/mapi_v2/Order/payVip", arguments);
	}

	this.getPayOrder = function() {
		httpService.simplePost('/mapi_v2/Order/getPayOrder', arguments);
	}
}]).
service('NoteService', ['httpService', function(httpService) {

	this.getNote = function(params, callback) {
		httpService.simpleGet("/mapi_v2/Course/getOneNote", arguments);
	}

	this.getNoteList = function(params, callback) {
		httpService.get({
			url : app.host + '/mapi_v2/Course/getNoteList',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}
}]).
service('CouponService', ['httpService', function(httpService) {

	this.checkCoupon = function() {
		httpService.simplePost('/mapi_v2/Course/coupon', arguments);
	}
}]).
service('UserService', ['httpService', 'applicationProvider', function(httpService, applicationProvider) {

	this.follow = function(params, callback) {
		httpService.simplePost("/mapi_v2/User/follow", arguments);
	};

	this.unfollow = function(params, callback) {
		httpService.simplePost("/mapi_v2/User/unfollow", arguments);
	};

	this.searchUserIsFollowed = function(params, callback) {
		httpService.simpleGet("/mapi_v2/User/searchUserIsFollowed", arguments);
	}

	this.getUserTeachCourse = function(params,callback) {
		httpService.simpleGet("/mapi_v2/Course/getUserTeachCourse", arguments);
	}

	this.getLearningCourseWithoutToken = function(params, callback) {
		httpService.simpleGet("/mapi_v2/Course/getLearningCourseWithoutToken", arguments);
	}

	this.getUserInfo = function(params, callback) {
		httpService.simpleGet("/mapi_v2/User/getUserInfo", arguments);
	}

	this.smsSend = function(params, callback) {
		httpService.post({
			url : app.host + '/mapi_v2/User/smsSend',
			data : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}

	this.getCourseTeachers = function(params, callback) {
		httpService.get({
			url : app.host + '/mapi_v2/User/getCourseTeachers',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}

	this.regist = function(params, callback) {
		httpService.post({
			url : app.host + '/mapi_v2/User/regist',
			data : params,
			success : function(data, status, headers, config) {
				callback(data);
				if (data && !data.error) {
					applicationProvider.setUser(data.user, data.token);
				}
			},
			error : function(data) {
			}
		});
	}

	this.login = function(params, callback) {
		httpService.post({
			url : app.host + '/mapi_v2/User/login',
			data : params,
			success : function(data, status, headers, config) {
				callback(data);
				if (data && !data.error) {
					applicationProvider.setUser(data.user, data.token);
				}
			},
			error : function(data) {
			}
		});
	}

	this.logout = function(params, callback) {
		httpService.post({
			url : app.host + '/mapi_v2/User/logout',
			data : params,
			success : function(data, status, headers, config) {
				callback(data);
				applicationProvider.clearUser();
			},
			error : function(data) {
			}
		});
	}

}]).
service('ClassRoomService', ['httpService', function(httpService) {

	this.getClassRooms = function(params, callback) {
		httpService.get({
			url : app.host + '/mapi_v2/ClassRoom/getClassRooms',
			params : {
				limit : params.limit
			},
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}

	this.myClassRooms = function(params, callback) {
		httpService.get({
			url : app.host + '/mapi_v2/ClassRoom/myClassRooms',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}

}]).
service('QuestionService', ['httpService', function(httpService) {

	this.getCourseThreads = function(params, callback) {
		httpService.get({
			url : app.host + '/mapi_v2/Course/getCourseThreads',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}

	this.getThread = function(params, callback) {
		httpService.get({
			url : app.host + '/mapi_v2/Course/getThread',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}

	this.getThreadTeacherPost = function(params, callback) {
		httpService.get({
			url : app.host + '/mapi_v2/Course/getThreadTeacherPost',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}

	this.getThreadPost = function(params, callback) {
		httpService.get({
			url : app.host + '/mapi_v2/Course/getThreadPost',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}
}]).
service('CourseService', ['httpService', function(httpService) {

	this.getCourseNotices = function(params, callback) {
		httpService.simpleGet("/mapi_v2/Course/getCourseNotices", arguments);
	}
	
	this.unLearnCourse = function(params, callback) {
		httpService.simpleGet("/mapi_v2/Course/unLearnCourse", arguments);
	}
	
	this.vipLearn = function(params, callback) {
		httpService.simpleGet("/mapi_v2/Course/vipLearn", arguments);
	}

	this.favoriteCourse = function(params, callback) {
		httpService.simplePost('/mapi_v2/Course/favoriteCourse', arguments);
	}

	this.unFavoriteCourse = function(params, callback) {
		httpService.simplePost('/mapi_v2/Course/unFavoriteCourse', arguments);
	}

	this.getCourseReviewInfo = function(params, callback) {
		httpService.simpleGet("/mapi_v2/Course/getCourseReviewInfo", arguments);
	}

	this.getReviews = function(params, callback) {
		httpService.get({
			url : app.host + '/mapi_v2/Course/getReviews',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}
	this.getLiveCourses = function(params, callback) {
		httpService.get({
			url : app.host + '/mapi_v2/Course/getLiveCourses',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}

	this.getLearningCourse = function(params, callback) {
		httpService.get({
			url : app.host + '/mapi_v2/Course/getLearningCourse',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}

	this.getFavoriteCourse = function(url, params, callback) {
		httpService.get({
			url : app.host + '/mapi_v2/' + url,
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}

	this.getAllLiveCourses = function(params, callback) {
		httpService.get({
			url : app.host + '/mapi_v2/Course/getAllLiveCourses',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
			}
		});
	}

	this.searchCourse = function(params, callback, error) {
		httpService.get({
			url : app.host + '/mapi_v2/Course/searchCourse',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
				if (error) {
					error(data)
				}
			}
		});
	}

	this.getCourse = function(params, callback) {
		httpService.simpleGet("/mapi_v2/Course/getCourse", arguments);
	}

	this.getCourses = function(params, callback, error) {
		httpService.get({
			url : app.host + '/mapi_v2/Course/getCourses',
			params : {
				limit : params.limit,
				start: params.start,
				categoryId : params.categoryId,
				sort : params.sort
			},
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
				if (error) {
					error(data)
				}
			}
		});
	}
}]).
service('SchoolService', ['httpService', function(httpService) {

	this.getLiveLatestCourses = function(params, callback) {
		httpService.simpleGet("/mapi_v2/School/getLiveLatestCourses", arguments);
	}

	this.getLiveRecommendCourses = function(params, callback) {
		httpService.simpleGet("/mapi_v2/School/getLiveRecommendCourses", arguments);
	}

	this.getLatestCourses = function(params, callback) {
		httpService.simpleGet("/mapi_v2/School/getLatestCourses", arguments);
	}

	this.getVipPayInfo = function(params, callback) {
		httpService.simpleGet("/mapi_v2/School/getVipPayInfo", arguments);
	}

	this.getSchoolVipList = function(params, callback) {
		httpService.simpleGet('/mapi_v2/School/getSchoolVipList', arguments);
	}

	this.getSchoolPlugins = function(params, callback) {
		httpService.simpleGet('/mapi_v2/School/getSchoolPlugins', arguments);
	}

	this.getSchoolBanner = function(callback) {
		httpService.get({
			url : app.host + '/mapi_v2/School/getSchoolBanner',
			success : function(data, status, headers, config) {
				callback(data);
			}
		});
	}

	this.getRecommendCourses = function(params, callback) {

		httpService.get({
			url : app.host + '/mapi_v2/School/getRecommendCourses',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			}
		});
	}
}]).
service('platformUtil', function() {
	var browser = {
	    v: (function(){
	        var u = navigator.userAgent, p = navigator.platform;
	        return {
	            trident: u.indexOf('Trident') > -1, //IE内核
	            presto: u.indexOf('Presto') > -1, //opera内核
	            webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
	            gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
	            mobile: !!u.match(/AppleWebKit.*Mobile.*/), //是否为移动终端
	            ios: !!u.match(/i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
	            android: u.indexOf('Android') > -1, //android终端
	            iPhone: u.indexOf('iPhone') > -1 , //是否为iPhone或者QQHD浏览器
	            iPad: u.indexOf('iPad') > -1, //是否iPad
	            weixin: u.indexOf('MicroMessenger') > -1, //是否微信
	            webApp: u.indexOf('Safari') == -1, //是否web应用程序，没有头部与底部
	            UCB: u.match(/UCBrowser/i) == "UCBrowser",
	            QQB: u.match(/MQQBrowser/i) == "MQQBrowser",
	            win: p.indexOf('Win') > -1,//判断是否是WIN操作系统
	            mac: p.indexOf('Mac') > -1,//判断是否是Mac操作系统
	            native: u.indexOf('kuozhi') > -1, //是否native应用程序，没有头部与底部
	        };
	    })()
	};
	
	this.native = browser.v.native;

	this.browser = browser;

}).
service('httpService', ['$http', '$rootScope', 'platformUtil', function($http, $rootScope, platformUtil) {
	
	var self = this;
	this.getOptions = function(method, url, params, callback, errorCallback) {
		var options = {
			method : method,
			url : app.host + url,
			headers : { "token" : $rootScope.token },
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
				if (errorCallback) {
					errorCallback(data);
				} 
			}
		}

		if (method == "get") {
			options["params"] = params;
		} else if (method == "post") {
			options["data"] = params;
		}

		return options;
	};

	this.simpleRequest = function(method, url, params, callback, errorCallback) {
		var options = self.getOptions(method, url, params, callback, errorCallback);
		var http = $http(options).success(options.success);

		if (options.error) {
			http.error(options.error);
		} else {
			http.error(function(data) {
				console.log(data);
			});
		}
	}

	this.simplePost = function(url) {
		params  = arguments[1][0];
		callback = arguments[1][1];
		errorCallback = arguments[1][2];

		if (platformUtil.native) {
			esNativeCore.post(url,  { "token" : $rootScope.token } , params );
		} else {
			self.simpleRequest("post", url, params, callback, errorCallback);
		}
	};

	this.simpleGet = function(url) {
		params  = arguments[1][0];
		callback = arguments[1][1];
		errorCallback = arguments[1][2];

		self.simpleRequest("get", url, params, callback, errorCallback);
	};

	this.get = function(options) {
		options.method  = "get";
		options.headers = { "token" : $rootScope.token };

		var http = $http(options).success(options.success);

		if (options.error) {
			http.error(options.error);
		} else {
			http.error(function(data) {
				console.log(data);
			});
		}
	}

	this.post = function(options) {

		options.method  = "post";
		options.headers = { "token" : $rootScope.token };

		var angularPost = function(options) {
			var http = $http(options).success(options.success);
			if (options.error) {
				http.error(options.error);
			} else {
				http.error(function(data) {
					console.log(data);
				});
			}
		};

		if (platformUtil.native) {
			esNativeCore.post(options.url, options.headers, options.data);
		} else {
			angularPost(options);
		}
	}
}]);