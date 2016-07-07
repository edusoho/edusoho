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
service('ArticleService', ['httpService', function(httpService) {

	this.getArticle = function(callback) {
		httpService.apiGet("/api/articles/" + arguments[0]['id'], arguments);
	}
}]).
service('LessonLiveService', ['httpService', function(httpService) {

	this.createLiveTickets = function(callback) {
		httpService.apiPost("/api/lessons/" + arguments[0]['lessonId'] + "/live_tickets", arguments);
	}

	this.getLiveInfoByTicket = function(callback) {
		httpService.apiGet("/api/lessons/" + arguments[0]['lessonId'] + "/live_tickets/" + arguments[0]['ticket'], arguments);
	}

	this.getLiveReplay = function() {
		httpService.apiGet("/api/lessons/" + arguments[0]['id'] + "/replay", arguments);
	}

}]).
service('AnalysisService', ['httpService', function(httpService) {

	this.getCourseChartData = function(callback) {
		httpService.apiGet("/api/analysis/Course/learnDataByDay?courseId" + arguments[0]['courseId'], arguments);
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

	this.createOrder = function(params, callback) {
		httpService.simplePost("/mapi_v2/Order/createOrder", arguments);
	}

	this.payVip = function(params, callback) {
		httpService.simpleGet("/mapi_v2/Order/payVip", arguments);
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
service('HomeworkManagerService', ['httpService', function(httpService) {

	this.teachingResult = function() {
		httpService.apiGet("/api/homework/manager/teaching", arguments);
	};

	this.showCheck = function() {
		httpService.apiGet("/api/homework/manager/check/" + arguments[0]['homeworkResultId'], arguments);
	}
}]).
service('ThreadManagerService', ['httpService', function(httpService) {

	this.questionResult = function() {
		httpService.apiGet("/api/thread/manager/question", arguments);
	}
}]).
service('UserService', ['httpService', 'applicationProvider', function(httpService, applicationProvider) {

	this.uploadAvatar = function(params, callback) {
		httpService.muiltPost({
			url : app.host + '/mapi_v2/User/uploadAvatar',
			data : params,
			success : callback
		});
	};

	this.updateUserProfile = function(params, callback) {
		httpService.simplePost("/mapi_v2/User/updateUserProfile", arguments);
	};

	this.getUserInfo = function(params, callback) {
		httpService.simpleGet("/mapi_v2/User/getUserInfo", arguments);
	};

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

	this.search = function(params, callback) {
		httpService.simpleGet("/mapi_v2/ClassRoom/search", arguments);
	}

	this.learnByVip = function(params, callback) {
		httpService.simpleGet("/mapi_v2/ClassRoom/learnByVip", arguments);
	}
	
	this.sign = function(params, callback) {
		httpService.simpleGet("/mapi_v2/ClassRoom/sign", arguments);
	}

	this.getTodaySignInfo = function(params, callback) {
		httpService.simpleGet("/mapi_v2/ClassRoom/getTodaySignInfo", arguments);
	}

	this.getAnnouncements = function(params, callback) {
		httpService.simpleGet("/mapi_v2/ClassRoom/getAnnouncements", arguments);
	}
	
	this.unLearn = function(params, callback) {
		httpService.simpleGet("/mapi_v2/ClassRoom/unLearn", arguments);
	}

	this.getTeachers = function(params, callback) {
		httpService.simpleGet("/mapi_v2/ClassRoom/getTeachers", arguments);
	}

	this.getStudents = function(params, callback) {
		httpService.apiGet("/api/classrooms/" + arguments[0]['classRoomId'] + "/members", arguments);
		//httpService.simpleGet("/mapi_v2/ClassRoom/getStudents", arguments);
	}

	this.getReviewInfo = function(params, callback) {
		httpService.simpleGet("/mapi_v2/ClassRoom/getReviewInfo", arguments);
	}

	this.getReviews = function(params, callback) {
		httpService.simpleGet("/mapi_v2/ClassRoom/getReviews", arguments);
	}

	this.getClassRoomCourses = function(params, callback) {
		httpService.simpleGet("/mapi_v2/ClassRoom/getClassRoomCoursesAndProgress", arguments);
	}

	this.getLatestClassrooms = function(params, callback) {
		httpService.simplePost("/mapi_v2/ClassRoom/getLatestClassrooms", arguments);
	}

	this.getRecommendClassRooms = function(params, callback) {
		httpService.simplePost("/mapi_v2/ClassRoom/getRecommendClassRooms", arguments);
	}

	this.searchClassRoom = function(params, callback) {
		httpService.simpleGet("/mapi_v2/ClassRoom/getClassRooms", arguments);
	}

	this.getClassRoom = function(params, callback) {
		httpService.simpleGet("/mapi_v2/ClassRoom/getClassRoom", arguments);
	}

	this.getLearnClassRooms = function(params, callback) {
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
	
	this.getStudents = function(params, callback) {
		httpService.apiGet("/api/courses/" + arguments[0]['courseId'] + "/members", arguments);
	}

	this.updateModifyInfo = function(params, callback) {
		httpService.simplePost("/mapi_v2/Course/updateModifyInfo", arguments);
	}

	this.getModifyInfo = function(params, callback) {
		httpService.simpleGet("/mapi_v2/Course/getModifyInfo", arguments);
	}
	
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
service('httpService', ['$http', '$rootScope', 'platformUtil', '$q', 'cordovaUtil', function($http, $rootScope, platformUtil, $q, cordovaUtil) {
	
	var self = this;
	this.filterCallback = function(data, callback) {
		if ("AuthToken is not exist." == data.message) {
			cordovaUtil.sendNativeMessage("token_lose", {});
			return;
		}

		if (data.error && "not_login" == data.error.name) {
			cordovaUtil.sendNativeMessage("token_lose", {});
			return;
		}
		callback(data);
	};

	this.getOptions = function(method, url, params, callback, errorCallback) {
		var options = {
			method : method,
			url : app.host + url,
			headers : { "token" : $rootScope.token },
			success : function(data, status, headers, config) {
				self.filterCallback(data, callback);
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

	this.nativePost = function(options) {
		esNativeCore.post($q, options.url,  options.headers , options.data )
		.then(function(data) {
			self.filterCallback(angular.fromJson(data), options.success);
		}, function(error) {
			self.filterCallback(angular.fromJson(error), options.error);
		});
	};

	this.simplePost = function(url) {
		params  = arguments[1][0];
		callback = arguments[1][1];
		errorCallback = arguments[1][2];

		if (platformUtil.native) {
			var options = {
				url : app.host + url,
				headers : { "token" : $rootScope.token },
				data : params,
				success : callback,
				error : errorCallback
			}
			self.nativePost(options);
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
		options.headers = options.headers || {};
		options.headers["token"] = $rootScope.token;

		var http = $http(options).success(function(data) {
			self.filterCallback(data, options.success);
		});

		if (options.error) {
			http.error(options.error);
		} else {
			http.error(function(data) {
				console.log(data);
			});
		}
	}

	this.apiGet = function(url) {

		params  = arguments[1][0];
		callback = arguments[1][1];
		errorCallback = arguments[1][2];

		var options = self.getOptions("get", url, params, callback, errorCallback);
		options.headers['Auth-Token'] = options.headers['token'];
		options.headers['token'] = null;
		var http = $http(options).success(options.success);

		if (options.error) {
			http.error(options.error);
		} else {
			http.error(function(data) {
				console.log(data);
			});
		}
	}

	this.apiPost = function(url) {

		params  = arguments[1][0];
		callback = arguments[1][1];
		errorCallback = arguments[1][2];

		var options = self.getOptions("post", url, params, callback, errorCallback);
		options.headers['Auth-Token'] = options.headers['token'];
		options.headers['token'] = null;
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
		options.headers = options.headers || {};
		options.headers["token"] = $rootScope.token;

		var angularPost = function(options) {
			var http = $http(options).success(function(data) {
				self.filterCallback(data, options.success);
			});
			if (options.error) {
				http.error(options.error);
			} else {
				http.error(function(data) {
					console.log(data);
				});
			}
		};

		if (platformUtil.native) {
			self.nativePost(options);
		} else {
			angularPost(options);
		}
	}

	this.muiltPost = function(options) {
		var headers = options.headers || {};
		headers["token"] = $rootScope.token;
		headers['Content-Type'] = undefined;

		var fd = new FormData();
		for (var key in options.data) {
			fd.append(key, options.data[key]);
		}

		$http.post(
			options.url, 
			fd, 
			{
            	transformRequest: angular.identity,
            	headers: headers
            }
        ).success(function(data){
        	options.success(data);
        }).error(function(error){
        	console.log(error);
        });
	}
}]);