var app = angular.module('app', [
            'ngSanitize',
            'ui.router',
            'AppService',
            'AppFactory',
            'AppProvider',
            'ngSideView',
            'pasvaz.bindonce'
  ]);

app.version = "1.1.0";
app.viewFloder = "/bundles/topxiamobilebundlev2/main/";

app.config(['$httpProvider', function($httpProvider) {

    $httpProvider.defaults.headers.put['Content-Type'] = 'application/x-www-form-urlencoded';
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
    // Override $http service's default transformRequest
    $httpProvider.defaults.transformRequest = [function(data) {

        /**
         * The workhorse; converts an object to x-www-form-urlencoded serialization.
         * @param {Object} obj
         * @return {String}
         */
        var param = function(obj) {
            var query = '';
            var name, value, fullSubName, subName, subValue, innerObj, i;
 
            for (name in obj) {
                value = obj[name];
 
                if (value instanceof Array) {
                    for (i = 0; i < value.length; ++i) {
                        subValue = value[i];
                        fullSubName = name + '[' + i + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                } else if (value instanceof Object) {
                    for (subName in value) {
                        subValue = value[subName];
                        fullSubName = name + '[' + subName + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                } else if (value !== undefined && value !== null) {
                    query += encodeURIComponent(name) + '='
                            + encodeURIComponent(value) + '&';
                }
            }
 
            return query.length ? query.substr(0, query.length - 1) : query;
        };
 
        return angular.isObject(data) && String(data) !== '[object File]'
                ? param(data)
                : data;
    }];
}]);

app.config([ 'appRouterProvider', '$urlRouterProvider', function(appRouterProvider, $urlRouterProvider)
{
  $urlRouterProvider.when("/", "/index").
  otherwise('/index');

  appRouterProvider.init();
}]);

app.run(["applicationProvider", "$rootScope", '$timeout', 'platformUtil',
  function(applicationProvider, $rootScope, $timeout, platformUtil) {

  $rootScope.platform = platformUtil;
  $rootScope.showLoad = function(template) {
    if ($rootScope.loadPop) {
      $rootScope.loadPop.loading("hide");
    }
    $rootScope.loadPop = $.loading({
        content: "加载中...",
    });

    $timeout(function(){
        if ($rootScope.loadPop) {
          $rootScope.loadPop.loading("hide");
        }
    },5000);
  };

  $rootScope.toast = function(template) {
    var el = $.tips({
        content: template,
        stayTime: 2000,
        type: "info"
    });

    $timeout(function(){
        el.loading("hide");
    },2000);
  };

  $rootScope.hideLoad = function() {
    if (! $rootScope.loadPop) {
      return;
    }
    $rootScope.loadPop.loading("hide");
    $rootScope.loadPop = null;
  };

  app.host = window.location.origin;
  app.rootPath = window.location.origin + window.location.pathname;
  $rootScope.stateParams = {};

  applicationProvider.init(app.host);
  FastClick.attach(document.body);
}]);

angular.element(document).ready(function() {
    var platformUtil = angular.injector(["AppFactory", "ng"]).get("platformUtil");
    if (platformUtil.native) {
      document.addEventListener("deviceready", function() {
          angular.bootstrap( document, ["app"] );
      });
      return;
    }
    
    angular.bootstrap( document, ["app"] );
});
;
var appFactory = angular.module('AppFactory', []);
appFactory.factory('AppUtil', ['$timeout', function($timeout) {
	var utils = {
		formatString : function(str) {
			var args = arguments, re = new RegExp("%([1-" + args.length + "])", "g");
			return String(str).replace(re, function($1, $2) {
				return args[$2];
			});
		},
		createArray : function(count) {
			var arr = [];
			for (var i = count- 1; i >= 0; i--) {
				arr.unshift(i);
			};

			return arr;
		},
		coverCategoty : function(categoryTree) {
			var categorys = [];
			for (var i = categoryTree.length - 1; i >= 0; i--) {
				categorys.unshift(categoryTree[i]);
			};
		},
		showPop : function(opts, callback) {
			var confirmPopup = $ionicPopup.confirm({
			     title: opts.title,
			     template: opts.template,
			     okText : opts.okText || "确定",
			     cancelText : opts.cancelText || "取消"
			});
			
			confirmPopup.then(function(res) {
				callback(res);
			});
		},
		createDialog : function(title, template, btns, modalInitFunc) {
			
			var dia=$.dialog({
			        title: title,
			        content: template,
			        button: btns || ["确认"]
			});

			dia.on("dialog:action",function(e){
			       modalInitFunc(e.index);
			       dia.dialog("hide");
			});
		},
		inArray : function(elem, arr, i) {
			    var len;
			    var deletedIds = [];
			    var indexOf = deletedIds.indexOf;
			    if ( arr ) {
			        if ( indexOf ) {
			            return indexOf.call( arr, elem, i );
			        }
			        len = arr.length;
			        i = i ? i < 0 ? Math.max( 0, len + i ) : i : 0;
			        for ( ; i < len; i++ ) {
			            if ( i in arr && arr[ i ] === elem ) {
			                return i;
			            }
			        }
			    }

			    return -1;
		}
	};
	
	return utils;
}]).
factory('ServcieUtil', function() {

	return {
		getService : function(name) {
			return angular.injector(["AppService", "ng"]).get(name);
		}
	}
}).
factory('ClassRoomUtil', function() {
	var getService = function() {
		return {
			"homeworkReview" : {
				title : "24小时作业批改",
				class : "homeworkReview",
				name : "练"
			},
			"testpaperReview" : {
				title : "24小时阅卷点评",
				class : "testpaperReview",
				name : "试"
			},
			"teacherAnswer" : {
				title : "提问必答",
				class : "teacherAnswer",
				name : "问"
			},
			"liveAnswer" : {
				title : "一对一在线答疑",
				class : "liveAnswer",
				name : "疑"
			},
			"event" : {
				title : "班级活动",
				class : "event",
				name : "动"
			},
			"workAdvise" : {
				title : "就业指导",
				class : "workAdvise",
				name : "业"
			},
		};
	};

	var filter = function(classRoom) {
		var classRoomService = classRoom.service;
		var service = getService();
		if (!classRoomService || classRoomService == "null") {
			classRoom.service = service;
			return classRoom;
		}
		for (var j = 0; j < classRoomService.length; j++) {
			service[classRoomService[j]].class = "active";
		};
		classRoom.service = service;

		return classRoom;
	};

	var cover = function(classRoom) {
		var classRoomService = classRoom.service;
		var service = getService();
		if (!classRoomService || classRoomService == "null") {
			classRoom.service = [];
			return classRoom;
		}
		for (var i = 0; i < classRoomService.length; i++) {
			classRoomService[i] = service[classRoomService[i]];
		};

		return classRoom;
	};

	return {
		filterClassRoom : function(classRoom) {
			return cover(classRoom);
		},
		filterClassRooms : function(classRooms) {
				for (var i = 0; i < classRooms.length; i++) {
					var classRoomService = classRooms[i].service;
					classRooms[i] = cover(classRooms[i]);
				};

				return classRooms;
			}
	}
}).
factory('VipUtil', function() {

	var payByYear = {
		title : "按年购买",
		type : 20,
		name : "year"
	};

	var payByMonth  ={
		title : "按月购买",
		type : 30,
		name : "month"
	};

	return {
		getPayType : function() {
			return {
				byYead : 20,
				byMonth : 30
			}
		},
		getPayMode : function(buyType) {
			
			if (buyType == 10) {
				return [payByYear, payByMonth];
			} else if (buyType == 20) {
				return [payByYear];
			} else {
				return [payByMonth];
			}
		}
	}
}).
factory('broadCast', ['$rootScope', function($rootScope) {
	angular.broadQueue = [];
	var broadCastService = {
		bind : function(event, callback){
			angular.broadQueue[event] = callback;
		},
		send : function(event, msg){
			callback = angular.broadQueue[event];
			//delete angular.broadQueue[event];
			callback(event, msg);
		}
	};
	return broadCastService;
}]).
factory('CourseUtil', ['$rootScope', 'CourseService', 'ClassRoomService' ,function(
	$rootScope, CourseService, ClassRoomService) {
	
	return {
		getFavoriteListTypes : function() {
			return {
				'course' : {
					url : "Course/getFavoriteNormalCourse",
					data : undefined,
					start : 0,
					canLoad : true
				},
				'live' : {
					url : "Course/getFavoriteLiveCourse",
					data : undefined,
					start : 0,
					canLoad : true
				}
			}
		},

		getCourseListTypes  : function() {
			return [
		  		{
		  			name : "课程",
		  			type : "normal"
		  		},
		  		{
		  			name : "直播",
		  			type : "live"
		  		}
		  	]
		},

		getCourseListSorts : function() {
			return [
		  		{
		  			name : "最新",
		  			type : "latest"
		  		},
		  		{
		  			name : "最热",
		  			type : "popular"
		  		},
		  		{
		  			name : "推荐",
		  			type : "recommendedSeq"
		  		}
		  	]
		},

		getClassRoomListSorts : function() {
			return [
		  		{
		  			name : "最新",
		  			type : "createdTime"
		  		},
		  		{
		  			name : "推荐",
		  			type : "recommendedSeq"
		  		}
		  	]
		}
	};
}]).
factory('platformUtil', function() {
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
	
	return browser.v;
}).
factory('cordovaUtil', ['$rootScope', 'sideDelegate', 'localStore', 'platformUtil', 
	function($rootScope, sideDelegate, localStore, platformUtil){
	var cordovaUtil =  {
		learnCourseLesson : function(courseId, lessonId) {
			alert("请在客户端学习非图文课时");
		},
		share : function(url, title, about, pic) {
			alert("请在客户端分享");
		},
		openDrawer : function(state) {
			sideDelegate.toggleMenu();
		},
		openWebView : function(url) {
			window.location.href = url;
		},
		pay : function(title, url) {
			alert("请在客户端内支付!");
		},
		getUserToken : function($q) {
			var deferred = $q.defer();
			deferred.resolve({
				user : angular.fromJson(localStore.get("user")),
				token : localStore.get("token")
			});

			return deferred.promise;
		},
		clearUserToken : function() {
			localStore.remove("user");
			localStore.remove("token");
		},
		saveUserToken : function(user, token) {
			localStore.save("user", angular.toJson(user));
			localStore.save("token", token);
		},
		showDownLesson : function(courseId) {
			alert("请在客户端下载课时");
		}, 
		closeWebView : function() {
			window.history.back();
		},
		backWebView : function() {
			window.history.back();
		},
		openPlatformLogin : function(type) {
			alert("请在客户端内登录!");
		},
		showInput : function(title, content, type, successCallback) {
			alert("该功能仅支持客户端!");
		},
		startAppView : function(name, data) {
			alert("该功能仅支持客户端!");
		},
		updateUser : function(user) {
		},
		uploadImage : function($q, url, headers, params, acceptType) {
			var deferred = $q.defer();
			deferred.resolve(null);
			return deferred.promise;
		},
		redirect : function(body) {
			alert("请在app内转发分享");
		},
		getThirdConfig : function($q) {
			var deferred = $q.defer();
			deferred.resolve([]);

			return deferred.promise;
		},
		sendNativeMessage : function(type, data) {
			if ("token_lose" == type) {
				$rootScope.user = null;
				$rootScope.token = null;
				localStore.remove("user");
				localStore.remove("token");
				alert("登录信息失效，请重新登录");
			}
		}
	};

	var proxy = function() {
		var self = {};

		var isNative = platformUtil.native;
		for ( var func in cordovaUtil) {
			self[func] = isNative ? esNativeCore[func] : cordovaUtil[func];
		}

		return self;
	}
	
	return proxy();
}]);;
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
}]);;
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
});;
app.directive('onElementReadey', function ($parse, $timeout) {
    return {
        restrict: 'A',
        link : function(scope, element, attrs) {
          $timeout(function() {
            $parse(attrs.onElementReadey)(scope);
           }, 100);
        }
    };
}).
directive('uiServicePanel', function($timeout) {
  return {
        restrict: 'A',
        link : function(scope, element, attrs) {
          var list = element[0].querySelector(".ui-list");
          var btn = element[0].querySelector(".service-btn");
          var expandIcon = angular.element(element[0].querySelector(".service-icon"));

          var btnElement = angular.element(btn);
          btnElement.on('click', function(e) {
            
            var expand = btnElement.attr("expand");
            btnElement.attr("expand", "true" == expand ? "false" : "true");
            expandIcon.css("-webkit-transform", ("true" == expand ? "rotate(-180deg)" : "rotate(0)"));

            var length = list.children.length;
            for (var i = 2; i < length; i++) {
              list.children[i].style.display = ("true" == expand ? 'none' : 'block');
            };
          });
          //$(titleLabel).animate({ 'left' : left + 'px' }, 500, 'ease-out');
        }
    };
}).
directive('uiAutoPanel', function () {
  return {
        restrict: 'A',
        link : function(scope, element, attrs) {
          element.attr("close", "true");
          var autoBtn = element[0].querySelector(".ui-panel-autobtn");
          var content = element[0].querySelector(".ui-panel-content");

          scope.$watch(attrs.data, function(newValue) {
            if (newValue) {
              initAutoBtn();
            }
          });

          function initAutoBtn() {

            if (200 > content.offsetHeight) {
              autoBtn.style.display = 'none';
              return;
            }
            content.style.height = '200px';
            var expand = angular.element(autoBtn.querySelector(".autobtn-icon"));
            var autoBtnText = autoBtn.querySelector(".autobtn-text");
            
            angular.element(autoBtn).on('click', function() {
              var isClose = element.attr("close");
              if ("true" == isClose) {
                  autoBtnText.innerText = "合并";
                  content.style.overflow = 'auto';
                  content.style.height = 'auto';
                  expand.removeClass("icon-expandmore");
                  expand.addClass("icon-expandless");
                  element.attr("close", "false");
              } else {
                  autoBtnText.innerText = "展开";
                  content.style.overflow = 'hidden';
                  content.style.height = '200px';
                  expand.addClass("icon-expandmore");
                  expand.removeClass("icon-expandless");
                  element.attr("close", "true");
              }
            });
          }
        }
    };
}).
directive('uiTab', function ($parse) {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {

          var self = this;
          var scroller = element[0].querySelector('.ui-tab-content');
          var nav = element[0].querySelector('.ui-tab-nav');

          function itemOnLoadListener(currentItem) {
            var isFirstRun = currentItem.attr("isFirstRun");
            var itemOnLoad = currentItem.attr("ng-onload");
            if ("true" != isFirstRun) {
              if (itemOnLoad) {
                $parse(itemOnLoad)(scope);
              }
              
              currentItem.attr("isFirstRun", "true");
            }
          }

          if ("empty"  != attrs.select) {
            var childrenIndex = 0;
            var childrenElement;
            for (var i = 0; i < nav.children.length; i++) {
              if (angular.element(nav.children[i]).hasClass('current')) {
                childrenIndex = i;
                break;
              }
            };

            angular.element(scroller.children[childrenIndex]).addClass('current');
            angular.element(nav.children[childrenIndex]).addClass('current');
            itemOnLoadListener(angular.element(scroller.children[childrenIndex]));
          }

          this.currentPage = 0;
          scroller.style.width = "100%";

          this.itemWidth = scroller.children[0].clientWidth;
          this.scrollWidth = this.itemWidth * this.count;

          function changeTabContentHeight(height) {
              var tabContent = element[0].querySelector('.ui-tab-content');
              $(tabContent).height(height);
          }

          angular.forEach(nav.children, function(item) {
            angular.element(item).on("click", function(e) {

                var currentItem = $(item);
                var tagetHasCurrent = currentItem.hasClass('current');
                var tempCurrentPage = self.currentPage;
                self.currentPage = currentItem.index();

                $(nav).children().removeClass('current');
                $(scroller).children().removeClass('current');

                if (tempCurrentPage == self.currentPage && "empty"  == attrs.select && tagetHasCurrent) {
                  changeTabContentHeight(0);
                  scope.$emit("tabClick", {
                    index : self.currentPage,
                    isShow : false
                  });
                  return;
                }

                var currentScrooler = angular.element(scroller.children[self.currentPage]);
                currentItem.addClass('current');
                currentScrooler.addClass("current");

                itemOnLoadListener(currentScrooler);
                changeTabContentHeight("100%");
                scope.$emit("tabClick", {
                    index : self.currentPage,
                    isShow : true
                });
            });
          });

          if ("empty"  == attrs.select) {
              scope.$on("closeTab", function(event, data) {
                angular.element(scroller.children[self.currentPage]).removeClass('current');
                angular.element(nav.children[self.currentPage]).removeClass('current');
                changeTabContentHeight(0);
                scope.$emit("tabClick", {
                    index : self.currentPage,
                    isShow : false
                });
              });
          }
    }
  }
}).
directive('imgError', function($timeout) {
  return {
    restrict: 'A',
    compile: function(tElem, tAttrs) {
            return {
                post: function postLink(scope, element, attributes) {
                  var errorSrc = "";
                  switch (attributes.imgError) {
                    case "avatar":
                      errorSrc = app.viewFloder  + "img/avatar.png";
                      break;
                    case "course":
                      errorSrc = app.viewFloder  + "img/default_course.jpg";
                      break;
                    case "vip":
                      errorSrc = app.viewFloder  + "img/vip_default.png";
                      break;
                    case "classroom":
                      errorSrc = app.viewFloder  + "img/default_class.jpg";
                      break;
                  }

                  element.attr('src', errorSrc);
                  element.on("error", function(e) {
                    element.attr("src", errorSrc);
                    element.on("error", null);
                  });

                  if ("classroom" == attributes.imgError
                     && attributes.imgSrc.indexOf("course-large.png") != -1) {
                    return;
                  }
                  $timeout(function() {
                    element.attr('src', attributes.imgSrc);
                  }, 100);
                }
            };
    }
  }
}).
directive('ngImgShow', function() {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {
      setTimeout(function() {
        var imageArray = [];
        angular.forEach(element[0].getElementsByTagName("img"), function(item, i) {
          imageArray.push(item.src);
          item.alt = i;
          item.addEventListener("click", function() {
            esNativeCore.showImages(this.alt, imageArray);
          });
        });
      }, 200);   
    }
  }
}).
directive('back', function(cordovaUtil, $state) {
  return {
    restrict: 'A',
    compile: function(tElem, tAttrs) {
            return { 
                post: function postLink(scope, element, attributes) {

                  element.on("click", function(){
                    if (attributes["back"] == "go") {
                      cordovaUtil.backWebView();
                      return;
                    }
                    if (attributes["back"] == "close" && scope.close) {
                      scope.close();
                      return;
                    }
                    $state.go("slideView.mainTab");
                  });
                }
            };
    }
  }
}).
directive('ngHtml', function($window, $state) {
  return {
    restrict: 'A',
    compile: function(tElem, tAttrs) {
            return { 
                post: function postLink(scope, element, attributes) {
                  scope.$watch(attributes.ngHtml, function(newValue) {
                    element.html(newValue);
                  });
                }
           };
    }
  }
}).
directive('uiPop', function($window) {
  return {
    restrict: 'A',
    compile: function(tElem, tAttrs) {
            return { 
                post: function postLink(scope, element, attributes) {

                    function changePopStatus() {
                      scope.$apply(function() {
                        scope.isShowMenuPop = ! scope.isShowMenuPop;
                      });
                    }

                    var popBtn = element[0].querySelector(".ui-pop-btn");
                    var popBg = element[0].querySelector(".ui-pop-bg");

                    popBg.style.width = $window.innerWidth + "px";
                    popBg.style.height = $window.innerHeight + "px";
                    angular.element(popBg).on("click", function(e) {
                      changePopStatus();
                    });

                    angular.element(popBtn).on("click", function(e) {
                      changePopStatus();
                    });
                }
           };
    }
  }
}).
directive('uiBar', function() {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {
        var toolEL = element[0].querySelector(".bar-tool");
        var titleEL = element[0].querySelector(".title");
        
        var toolELWidth = toolEL ? toolEL.offsetWidth : 44;
        toolELWidth = toolELWidth < 44 ? 44 : toolELWidth;
        titleEL.style.paddingRight = toolELWidth + "px";
        titleEL.style.paddingLeft = toolELWidth + "px";
    }
  }
}).
directive('ngHref', function($window) {
  return {
    restrict: 'A',
    compile: function(tElem, tAttrs) {
            return function ngEventHandler(scope, element, attr) {
              element.on("click", function(e) {
                var url = [$window.location.origin, $window.location.pathname, attr.ngHref].join("");
                if (scope.platform.native) {
                  esNativeCore.openWebView(url);
                  return;
                }
                $window.location.href = url;
              });
            };
    }
  }
}).
directive('uiScroll', function($parse) {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {
      scope.$watch(attrs.data, function(newValue) {

          if (newValue) {
                if (angular.isArray(newValue) && newValue.length == 0) {
                  return;
                }
                var uiHead = element[0].querySelector(".ui-details-head");
                element.on("scroll", function() {
                  var scrollHeight = element[0].scrollHeight;
                  var scrollTop = element[0].scrollTop;
                  var clientHeight = element[0].clientHeight;

                  if (attrs.onScroll) {
                    scope.headTop = uiHead.offsetHeight;
                    scope.scrollTop = scrollTop;
                    $parse(attrs.onScroll)(scope);
                  }
                  if ( !scope.isLoading && ( (scrollTop + clientHeight) >= scrollHeight ) ) {
                    scope.isLoading = true;
                    $parse(attrs.onInfinite)(scope, { successCallback : function() {
                      scope.isLoading = false;
                    } });
                  }
                });
          }
      });
      
    }
  }
}).
directive('uiSliderBox', function($parse) {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {
          scope.$watch(attrs.uiSliderBox, function(newValue) {
            if (newValue && newValue.length > 0) {
                initSlider();
                if (attrs.onLoad) {
                  $parse(attrs.onLoad)(scope, element);
                }
            }
          });

          if ("true" != attrs.auto && element[0].clientWidth) {
            element.css('height', (element[0].clientWidth / 1.9) + "px");
          }
          
          function initSlider () {
              var slider = new fz.Scroll('.' + attrs.slider, {
                  role: 'slider',
                  indicator: true,
                  autoplay: false,
                  interval: 3000
              });

              slider.on('beforeScrollStart', function(fromIndex, toIndex) {
                if (attrs.scrollChange) {
                  scope.scrollIndex = toIndex;
                  $parse(attrs.scrollChange)(scope);
                }
              });

              slider.on('scrollEnd', function() {

              });
          }
          
    }
  }
}).
directive('slideIndex', function() {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {
          var total = 0;
          var $currentIndex = 0;
          scope.slideHasChanged = function($index) {
            $currentIndex = $index;
            changeSlidePointStatus();
          }

          scope.$watch("banners", function(newValue) {
            if (newValue && newValue.length > 0) {
                total = newValue.length;
                initSlideIndex();
            }
          });
          
          function changeSlidePointStatus()
          {
            angular.forEach(element[0].querySelectorAll('.point'), function(item, index){

              if (index == $currentIndex) {
                item.classList.add("active");
              } else {
                item.classList.remove("active");
              }
            });
          }

          function initSlideIndex() {
                var points = "";
            
                for (var i = 0 ; i < total; i++) {
                  if (i == $currentIndex) {
                    points += "<span class='point active'></span>";
                  } else {
                    points += "<span class='point'></span>";
                  }
                  
                };

                element.append("<p class='slide-index'>" + points + "</p>");
          }
    }
  }
}).
directive('lazyLoad', function () {
  return function(scope, elm, attr) {
            echo.init({
            root:elm[0],
            offset: 100,
            throttle: 250,
            unload: false,
            callback: function (element, op) {

            }
        });
    }
}).
directive('modal', function () {
  return {
    restrict: 'EA',
    priority : 10000,
    controller : function($scope, $element) {
    },
    link : function(scope, element, attrs) {
      element.addClass("ui-modal");
      element.addClass("item");
      element.on('click', function(event) {
        scope.$emit("closeTab", {});
        $(".ui-scroller").css("overflow","scroll");
      });

      scope.$on("tabClick", function(event, data) {
        if (!data.isShow) {
          $(".ui-scroller").css("overflow","scroll");
          return;
        }

        $(".ui-scroller").css("overflow","hidden");
      });

    }
  }
}).
directive('listEmptyView', function (AppUtil) {
  return {
    restrict: 'EA',
    link : function(scope, element, attrs) {
      var html = '<div class="list-empty"><a> <i class="icon iconfont icon-%1"></i> <span>%2</span> </a></div>';
      html = AppUtil.formatString(html, attrs.icon || "ebook", attrs.title);
      scope.$watch(attrs.data, function(newValue) {
        if (newValue && newValue.length == 0) {
          element.html(html);
        } else {
          element.html("");
        }
      });
    }
  }
}).
directive('categoryTree', function () {
    return {
        restrict: 'E',
        scope: {  
            data: '=data',
            listener : '=listener'
        }, 
        templateUrl: app.viewFloder + 'view/category_tree.html', 

        link : function(scope, element, attrs) {

          function initCategory($scope) {
            var realityDepth = $scope.data.realityDepth > 3 ? 3 : $scope.data.realityDepth - 1;
            var categoryCols = [];
            var categoryTree = $scope.data.data[0];
            for (var i = realityDepth- 1; i >= 0; i--) {
                categoryCols[i] = [];
            };

            var getRootCategory = function(categoryId) {
              return {
                name : "全部",
                id : categoryId ? categoryId : 0
              }
            };

            categoryCols[0] = [getRootCategory()].concat(categoryTree.childs);
            $scope.categoryCols = categoryCols;

            var changeItemBG = function(item) {
              var parentNode = item.parentNode;
              if (!parentNode) {
                return;
              }

              angular.forEach(parentNode.children, function(item) {
                angular.element(item).css("background", "none");
              });
            };

            $scope.selectCategory = function($event, category) {
                    
                    changeItemBG($event.srcElement.parentNode);
                    angular.element($event.srcElement.parentNode).css("background", "#ccc");
                    if (category.childs) {
                      for (var i = $scope.categoryCols.length- 1; i >= category.depth; i--) {
                          $scope.categoryCols[i] = [];
                      };
                      var categoryColIndex = category.depth;
                      if (category.depth > 2) {
                        categoryColIndex = 2;
                      }
                      $scope.categoryCols[categoryColIndex] = [getRootCategory(category.id)].concat(category.childs);
                      $event.stopPropagation();
                    } else {
                      $scope.listener(category);
                    }
            };
          }

          scope.$watch("data", function(newValue) {
            if (newValue) {
                initCategory(scope);
            }
          });
        }
    };
});;
var appProvider = angular.module('AppProvider', []);
appProvider.provider('applicationProvider', function() {

	var self = this;
	this.$get = function(localStore, $rootScope, $q, cordovaUtil) {
		var application = {
			host : null,
			user : null,
			token : null
		};

		application.setHost = function(host){
			this.host = host;
		}

		application.init = function(host) {
			application.setHost(host);
			cordovaUtil.getUserToken($q).then(function(data) {
				application.user = data.user;
				application.token = data.token;
      	application.updateScope($rootScope);
			});
		}

		application.clearUser = function() {
			this.user = null;
			this.token = null;
			$rootScope.user = null;
			$rootScope.token = null;
			cordovaUtil.clearUserToken();
		}

		application.setUser = function(user, token) {
			this.user = user;
			this.token = token;
			this.updateScope($rootScope);
			cordovaUtil.saveUserToken(user, token);
		}

		application.updateScope = function($scope) {
			$scope.user = application.user;
			$scope.token = application.token;
		}
	    	return application;
	  }
});

appProvider.provider('appRouter', function($stateProvider) {

	this.initPlugin = function($stateProvider) {
		$stateProvider.state('article', {
          url: "/article/:id",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/article.html",
              controller : ArticleController
            }
          }
        });
	};

	this.init = function() {
    var routingConfig = app.routingConfig || {};

    var state = $stateProvider.state;
    $stateProvider.state = function(name, args) {
      if (routingConfig.hasOwnProperty(name)) {
        args = routingConfig[name];
      }
      return state.call($stateProvider, name, args);
    };
    
		$stateProvider.state("slideView",{
            abstract: true,
            views : {
                "rootView" : {
                    templateUrl : app.viewFloder  + 'view/main.html',
                    controller : AppInitController
                }
            }
        }).state("slideView.mainTab",{
        	url : "/index",
        	views : {
          		"menuContent" : {
            		templateUrl : app.viewFloder  + 'view/main_content.html',
            		controller : FoundTabController
          	}
       }
       });

        $stateProvider.state('courseList', {
          url: "/courselist/:type/:categoryId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/course_list.html",
              controller: CourseListController
            }
          }
        }).state('courseList.type', {
          url: "/type",
          views: {
            'courselist-content': {
              templateUrl: app.viewFloder  + "view/courselist_type.html"
            }
          }
        }).state('courseList.sort', {
          url: "/sort",
          views: {
            'courselist-content': {
              templateUrl: app.viewFloder  + "view/courselist_sort.html"
            }
          }
        });

        $stateProvider.state('classRoomList', {
          url: "/classroomlist/:categoryId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/classroom_list.html",
              controller: ClassRoomListController
            }
          }
        });

        $stateProvider.state('login', {
          url: "/login/:goto",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/login.html",
              controller: LoginController
            }
          }
        }).state('regist', {
          url: "/regist",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/regist.html",
              controller: RegistController
            }
          }
        });

        $stateProvider.state('myinfo', {
          url: "/myinfo",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/myinfo.html",
              controller: MyInfoController
            }
          }
        });

        $stateProvider.state('mylearn', {
          url: "/mylearn",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/mylearn.html",
              controller : MyLearnController
            }
          }
        });

        $stateProvider.state('myfavorite', {
          url: "/myfavorite",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/myfavorite.html"
            }
          }
        })

        $stateProvider.state('setting', {
          url: "/setting",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/setting.html",
              controller : SettingController
            }
          }
        });
        $stateProvider.state('question', {
          url: "/question/:courseId/:threadId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/question.html",
              controller : QuestionController
            }
          }
        });

        $stateProvider.state('note', {
          url: "/note/:noteId/",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/note.html",
              controller : NoteController
            }
          }
        });

        $stateProvider.state('course', {
          url: "/course/:courseId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/course.html",
              controller : CourseController
            }
          }
        });

        $stateProvider.state('classroom', {
          url: "/classroom/:classRoomId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/classroom.html",
              controller : ClassRoomController
            }
          }
        });

        $stateProvider.state('courseDetail', {
          url: "/coursedetail/:courseId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/course_detail.html",
              controller : CourseDetailController
            }
          }
        });

        $stateProvider.state('teacherlist', {
          url: "/teacherlist/:targetType/:targetId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/teacher_list.html",
              controller : TeacherListController
            }
          }
        });

        $stateProvider.state('studentlist', {
          url: "/studentlist/:targetType/:targetId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/teacher_list.html",
              controller : StudentListController
            }
          }
        });

        $stateProvider.state('coursePay', {
          url: "/coursepay/:targetId/:targetType",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/course_pay.html",
              controller : CoursePayController
            }
          }
        });

        $stateProvider.state('courseCoupon', {
          url: "/coursecoupon/:courseId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/coupon.html",
              controller : CourseCouponController
            }
          }
        });

        $stateProvider.state('viplist', {
          url: "/viplist",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/vip_list.html",
              controller : VipListController
            }
          }
        });

        $stateProvider.state('courseSetting', {
          url: "/coursesetting/:courseId/:isLearn",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/course_setting.html",
              controller : CourseSettingController
            }
          }
        });

        $stateProvider.state('vipPay', {
          url: "/vippay/:levelId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/vip_pay.html",
              controller : VipPayController
            }
          }
        });

        $stateProvider.state('courseNotice', {
          url: "/coursenotice/:targetType/:targetId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/course_notice.html",
              controller : CourseNoticeController
            }
          }
        });

        $stateProvider.state('courseReview', {
          url: "/coursereview/:targetType/:targetId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/course_review.html",
              controller : CourseReviewController
            }
          }
        });

        $stateProvider.state('userInfo', {
          url: "/userinfo/:userId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/user_info.html",
              controller : UserInfoController
            }
          }
        });

        $stateProvider.state('lesson', {
          url: "/lesson/:courseId/:lessonId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/lesson.html",
              controller : LessonController
            }
          }
        });
        $stateProvider.state('search', {
          url: "/search",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/search.html",
              controller : SearchController
            }
          }
        });

        $stateProvider.state('todolist', {
          url: "/todolist/:courseId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/todolist.html",
              controller : TeacherTodoListController
            }
          }
        });

        $stateProvider.state('homeworkCheck', {
          url: "/homeworkcheck/:homeworkResultId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/homework_check.html",
              controller : HomeworkCheckController
            }
          }
        });

        $stateProvider.state('teachingThreadList', {
          url: "/teaching/threadlist/:courseId",
          views: {
            'rootView': {
              templateUrl: app.viewFloder  + "view/teaching_thread_list.html",
              controller : ThreadTeachingController
            }
          }
        });

	    this.initPlugin($stateProvider);
	}

	this.$get = function() {
		return {};
	}
});;
app.controller('AppInitController', ['$scope', '$state', 'sideDelegate', 'SchoolService', AppInitController]);

function baseController($scope)
{
	this.getService = function(name) {
		return angular.injector(["AppService", "ng"]).get(name);
	}
}

function AppInitController($scope, $state, sideDelegate, SchoolService)
{	
	console.log("AppInitController");
	$scope.toggle = function() {
	    	sideDelegate.toggleMenu();
	};

	$scope.showMyView = function(state) {
		if ($scope.user) {
			$state.go(state);
		} else {
			$state.go("login");
		}
	}

	SchoolService.getSchoolPlugins(null, function(data) {
		$scope.plugins = data;
	});
};
app.controller('ArticleController', ['$scope', '$state', '$stateParams', 'cordovaUtil', 'ArticleService', ArticleController]);

function ArticleController($scope, $state, $stateParams, cordovaUtil, ArticleService)
{	
	var self = this;

	this.filterContent = function(content, limit) {

		content = content.replace(/<\/?[^>]*>/g,'');
		content = content.replace(/[\r\n\s]+/g,'');
		if (content.length > limit) {
	       content = content.substring(0, limit);
	    }
	    
	    return content;
	}

	$scope.init = function() {
		$scope.showLoad();
		ArticleService.getArticle({
			id : $stateParams.id
		}, function(data) {
			$scope.hideLoad();
			$scope.article = data;
		});
	};

	$scope.refresh = function(popScope) {
		$scope.init();
		popScope.isShowMenuPop = !popScope.isShowMenuPop;
	}

	$scope.share = function() {
		cordovaUtil.share(
	        app.host + "/article/" + $scope.article.id, 
	        $scope.article.title, 
	        self.filterContent($scope.article.body, 20), 
	        $scope.article.picture
	    );  
	}

	$scope.redirect = function() {
		var url = [app.rootPath, "#article/", $scope.article.id ].join("");
		cordovaUtil.redirect({
			type : "news.redirect",
			fromType : "news",
			id : $scope.article.id,
			title : $scope.article.title,
			image : $scope.article.picture,
			content : self.filterContent($scope.article.body, 20),
			url : url,
			source : "self"
		});
	}
};
app.controller(
  'ClassRoomListController', 
  [
    '$scope', 
    '$stateParams', 
    '$state', 
    'CourseUtil', 
    'ClassRoomService', 
    'CategoryService', 
    'ClassRoomUtil',
     ClassRoomListController
  ]
);

function ClassRoomListController($scope, $stateParams, $state, CourseUtil, ClassRoomService, CategoryService, ClassRoomUtil)
{
    $scope.categoryTab = {
      category : "分类",
      type : "全部分类",
      sort : "综合排序",
    };

    $scope.canLoad = true;
    $scope.start = $scope.start || 0;

    console.log("ClassRoomListController");
      $scope.loadMore = function(successCallback){
            if (! $scope.canLoad) {
              return;
            }
           setTimeout(function() {
              $scope.loadClassRoomList($stateParams.sort, successCallback);
           }, 200);
         
      };

      $scope.loadClassRoomList = function(sort, successCallback) {
             $scope.showLoad();
              ClassRoomService.searchClassRoom({
                limit : 10,
                start: $scope.start,
                category : $stateParams.categoryId,
                sort : sort,
                type : $stateParams.type
              }, function(data) {
                $scope.hideLoad();
                if (successCallback) {
                  successCallback();
                }
                var length  = data ? data.data.length : 0;
                if (!data || length == 0 || length < 10) {
                    $scope.canLoad = false;
                }

                $scope.classRooms = $scope.classRooms || [];
                for (var i = 0; i < length; i++) {
                  $scope.classRooms.push(ClassRoomUtil.filterClassRoom(data.data[i]));
                };

                $scope.start += data.limit;
              });
      }

      $scope.courseListSorts = CourseUtil.getClassRoomListSorts();

      CategoryService.getCategorieTree(function(data) {
        $scope.categoryTree = data;
      });

      $scope.selectType = function(item) {
             $scope.$emit("closeTab", {});
             $scope.categoryTab.type = item.name;
             clearData();
             $stateParams.type  = item.type;
             setTimeout(function(){
                $scope.loadClassRoomList($scope.sort);
             }, 100);
      }

      function clearData() {
        $scope.canLoad = true;
        $scope.start = 0;
        $scope.classRooms = null;
      }

      $scope.selectSort = function(item) {
        $scope.$emit("closeTab", {});
        $scope.categoryTab.sort = item.name;
        $scope.sort = item.type;
        clearData();
        setTimeout(function(){
            $scope.loadClassRoomList(item.type);
         }, 100);
      }

      $scope.onRefresh = function() {
        clearData();
        $scope.loadClassRoomList($scope.sort);
      }

      $scope.categorySelectedListener = function(category) {
             $scope.$emit("closeTab", {});
             $scope.categoryTab.category = category.name;
             clearData();
             $stateParams.type = null;
             $stateParams.categoryId  =category.id;
             $scope.loadClassRoomList($scope.sort);
      }

      $scope.loadClassRoomList();
};
app.controller('CourseController', ['$scope', '$stateParams', 'CourseService', 'AppUtil', '$state', 'cordovaUtil', CourseController]);
app.controller('CourseDetailController', ['$scope', '$stateParams', 'CourseService', CourseDetailController]);
app.controller('CourseSettingController', ['$scope', '$stateParams', 'CourseService', 'ClassRoomService', CourseSettingController]);

function CourseReviewController($scope, $stateParams, CourseService, ClassRoomService)
{
  var self = this;
  $scope.canLoad = true;
  $scope.start = $scope.start || 0;

  $scope.loadMore = function(){
        if (! $scope.canLoad) {
          return;
        }
       setTimeout(function() {
          self.loadReviews();
       }, 200);
  };

  this.loadCourseReviews = function(callback) {
    CourseService.getReviews({
      start : $scope.start,
      limit : 50,
      courseId : $stateParams.targetId
    }, callback);
  };

  this.loadClassRoomReviews = function(callback) {
    ClassRoomService.getReviews({
      start : $scope.start,
      limit : 50,
      classRoomId : $stateParams.targetId
    }, callback);
  };

  this.initTargetService = function(targetType) {
    if (targetType == "course") {
      self.targetInfoService = this.loadCourseReviewInfo;
      self.targetService = this.loadCourseReviews;
    } else if (targetType == "classroom") {
      self.targetInfoService = this.loadClassRoomReviewInfo;
      self.targetService = this.loadClassRoomReviews;
    }
  };

  this.loadReviews = function() {
    self.targetService(function(data) {
      var length  = data ? data.data.length : 0;
      if (!data || length == 0 || length < 50) {
          $scope.canLoad = false;
      }

      $scope.reviews = $scope.reviews || [];
      for (var i = 0; i < length; i++) {
        $scope.reviews.push(data.data[i]);
      };

      $scope.start += data.limit;
    });
  };

  this.loadCourseReviewInfo = function() {
    CourseService.getCourseReviewInfo({
      courseId : $stateParams.targetId
    }, function(data) {
      $scope.reviewData = data;
    });
  }

  this.loadClassRoomReviewInfo = function() {
    ClassRoomService.getReviewInfo({
      classRoomId : $stateParams.targetId
    }, function(data) {
      $scope.reviewData = data;
    });
  }

  $scope.loadReviewResult = function() {

    self.targetInfoService();
    self.loadReviews();
  }
  
  this.initTargetService($stateParams.targetType);
}

function CourseSettingController($scope, $stateParams, CourseService, $window)
{
  $scope.isLearn = $stateParams.isLearn;
  $scope.exitLearnCourse = function(reason) {
    $scope.showLoad();
    CourseService.unLearnCourse({
      reason : reason,
      courseId : $stateParams.courseId
    }, function(data) {
      $scope.hideLoad();
      if (! data.error) {
        $window.history.back();
        setTimeout(function() {
          $scope.$emit("refresh", {});
        }, 10);
        
      } else {
        $scope.toast(data.error.message);
      }
    });
  }
}

function CourseDetailController($scope, $stateParams, CourseService)
{
  CourseService.getCourse({
      courseId : $stateParams.courseId
    }, function(data) {
      $scope.course = data.course;
  });
}

app.controller('CourseToolController', ['$scope', '$stateParams', 'OrderService', 'CourseService', 'cordovaUtil', '$state', CourseToolController]);

function BaseToolController($scope, OrderService, cordovaUtil)
{
  var self = this;

  this.payCourse = function(price, targetType, targetId) {
      OrderService.createOrder({
        payment : "alipay",
        payPassword : "",
        totalPrice : price,
        couponCode : "",
        targetType : targetType,
        targetId : targetId
      }, function(data) {
        if (data.paid == true) {
          console.log("reload");
          window.location.reload();
        } else {
          var error = "加入学习失败";
          if (data.error) {
            error = data.error.message;
          }
          $scope.toast(error);
        }
      }, function(error) {
        console.log(error);
      });
    }

  this.vipLeand = function(vipLevelId, callback) {
    if ($scope.user == null) {
      cordovaUtil.openWebView(app.rootPath + "#/login/course");
      return;
    }
    if ($scope.user.vip == null || $scope.user.vip.levelId < vipLevelId) {
      cordovaUtil.openWebView(app.rootPath + "#/viplist");
      return;
    }
    callback();
  }

  this.join = function(callback) {
      if ($scope.user == null) {
        cordovaUtil.openWebView(app.rootPath + "#/login/course");
        return;
      }

      callback();
    }

  this.favoriteCourse = function(callback) {
    if ($scope.user == null) {
      cordovaUtil.openWebView(app.rootPath + "#/login/course");
      return;
    }

    callback();
  }

  $scope.getVipTitle = function(vipLevelId) {
      var vipLevels = $scope.vipLevels;
      for (var i = 0; i < vipLevels.length; i++) {
        var level = vipLevels[i];
        if (level.id == vipLevelId) {
          return level.name;
        }
      };
      
      return "";
  }

  this.filterContent = function(content, limit) {

    content = content.replace(/<\/?[^>]*>/g,'');
    content = content.replace(/[\r\n\s]+/g,'');
    if (content.length > limit) {
         content = content.substring(0, limit);
      }
      
      return content;
  }

  $scope.isCanShowVip = function(vipLevelId) {
    if (vipLevelId <= 0) {
      return false;
    }
    return $scope.vipLevels.length <= 0;
  }
}

function CourseToolController($scope, $stateParams, OrderService, CourseService, cordovaUtil, $state)
{
    this.__proto__ = new BaseToolController($scope, OrderService, cordovaUtil);
    var self = this;

    this.goToPay = function() {
      var course = $scope.course;
      var priceType = course.priceType;
      var price = "Coin" == priceType ? course.coinPrice : course.price;
      if (price <= 0) {
        self.payCourse(price, "course", $stateParams.courseId);
      } else {
        $state.go("coursePay", { targetId : $scope.course.id, targetType : 'course' });
      }
    };

    this.checkModifyUserInfo = function(modifyInfos) {
      for (var i = 0; i < modifyInfos.length; i++) {
        var modifyInfo = modifyInfos[i];
        if (!modifyInfo["content"] || modifyInfo["content"] == 0) {
          alert("请填写" + modifyInfo["title"]);
          return false;
        }
      };

      return true;
    }

    $scope.$parent.updateModifyInfo = function() {
      var modifyInfos = $scope.$parent.modifyInfos;
      if (!self.checkModifyUserInfo(modifyInfos)) {
        return;
      }
      $scope.showLoad()
      CourseService.updateModifyInfo({
        targetId : $scope.course.id
      }, function(data) {
        $scope.hideLoad();
        if (data.error) {
          $scope.toast(data.error.message);
          return;
        }
        if (true == data) {
          self.goToPay();
        }
      });
    };

    this.getModifyUserInfo = function(success) {
      $scope.$parent.close = function() {
        self.dialog.dialog("hide");
      }

      CourseService.getModifyInfo({}, function(data) {

        if(true != data["buy_fill_userinfo"]) {
          success();
          return
        }

        $scope.$parent.modifyInfos = data["modifyInfos"];
        if (data["modifyInfos"].length > 0) {
          self.dialog = $(".ui-dialog");
          self.dialog.dialog("show");
        } else {
          success();
        }
      });
    };

    $scope.vipLeand = function() {
      self.vipLeand($scope.course.vipLevelId, function() {
        CourseService.vipLearn({
          courseId : $stateParams.courseId
        }, function(data){
          if (! data.error) {
            window.location.reload();
          } else {
            $scope.toast(data.error.message);
          }
        }, function(error) {
          console.log(error);
        });
      });
    }

    $scope.joinCourse = function() {
      self.join(function() {
        self.goToPay();
      });

    }

    $scope.favoriteCourse = function() {

      self.favoriteCourse(function() {
        var params = {
            courseId : $stateParams.courseId
        };

        if ($scope.isFavorited) {
          CourseService.unFavoriteCourse(params, function(data) {
            if (data == true) {
              $scope.isFavorited = false;
            }
          });
        } else {
          CourseService.favoriteCourse(params, function(data) {
            if (data == true) {
              $scope.isFavorited = true;
            }
          });
        }
      });
    };

    $scope.shardCourse = function() {
      var about = $scope.course.about;

      cordovaUtil.share(
        app.host + "/course/" + $scope.course.id, 
        $scope.course.title, 
        self.filterContent(about, 20), 
        $scope.course.largePicture
      );      
    }

    $scope.continueLearnCourse = function() {
      $scope.$root.$emit("continueLearnCourse", {});
    };
}

function CourseController($scope, $stateParams, CourseService, AppUtil, $state, cordovaUtil)
{
    $scope.showLoad();

    CourseService.getCourse({
      courseId : $stateParams.courseId
    }, function(data) {
      if (data && data.error) {
        var dia = $.dialog({
                title : '课程预览' ,
                content : data.error.message,
                button : [ "确认" ]
        });

        dia.on("dialog:action",function(e){
                cordovaUtil.closeWebView();
        });
        return;
      }
      $scope.ratingArray = AppUtil.createArray(5);
      $scope.vipLevels = data.vipLevels;
      $scope.course = data.course;
      $scope.member = data.member;
      $scope.isFavorited = data.userFavorited;
      $scope.discount = data.discount;
      $scope.teachers = data.course.teachers;

      if (data.member) {
        var progress = data.course.lessonNum == 0 ? 0 : data.member.learnedNum / data.course.lessonNum;
        $scope.learnProgress = ((progress * 100).toFixed(2)) + "%" ;
      }

      $scope.courseView = app.viewFloder + (data.member ? "view/course_learn.html" : "view/course_no_learn.html");
      $scope.hideLoad();
    });

    $scope.loadReviews = function(){
      CourseService.getReviews({
        courseId : $stateParams.courseId,
        limit : 1
      }, function(data) {
        $scope.reviews = data.data;
      });
    }

    $scope.exitLearnCourse = function(reason) {
      $scope.showLoad();
      CourseService.unLearnCourse({
        reason : reason,
        courseId : $stateParams.courseId
      }, function(data) {
        $scope.hideLoad();
        if (! data.error) {
          window.location.reload();
        } else {
          $scope.toast(data.error.message);
        }
      });
    }

    $scope.showDownLesson = function() {
      cordovaUtil.showDownLesson($scope.course.id);
    }

    $scope.$parent.$on("refresh", function(event, data) {
      window.location.reload();
    });

    $scope.isCanShowConsultBtn = function() {
      if (! $scope.user) {
        return false;
      }
      
      if ("classroom" == $scope.course.source) {
        return false;
      }

      if (!$scope.teachers || $scope.teachers.length == 0) {
        return false;
      }

      return true;
    };

    $scope.consultCourseTeacher = function() {
      if (!$scope.teachers || $scope.teachers.length == 0) {
        alert("该课程暂无教师");
        return;
      }

      var userId = $scope.teachers[0].id;
      cordovaUtil.startAppView("courseConsult", { userId : userId });
    };
}

app.controller('ClassRoomController', ['$scope', '$stateParams', 'ClassRoomService', 'AppUtil', '$state', 'cordovaUtil', 'ClassRoomUtil', ClassRoomController]);
app.controller('ClassRoomCoursesController', ['$scope', '$stateParams', 'ClassRoomService', '$state', ClassRoomCoursesController]);
app.controller('ClassRoomToolController', ['$scope', '$stateParams', 'OrderService', 'ClassRoomService', 'cordovaUtil', '$state', ClassRoomToolController]);

function ClassRoomToolController($scope, $stateParams, OrderService, ClassRoomService, cordovaUtil, $state)
{
  this.__proto__ = new BaseToolController($scope, OrderService, cordovaUtil);
    var self = this;

    $scope.signDate = new Date();
    this.goToPay = function() {
      var classRoom = $scope.classRoom;
      var priceType = classRoom.priceType;
      var price = "Coin" == priceType ? classRoom.coinPrice : classRoom.price;
      if (price <= 0) {
        self.payCourse(price, "classroom", $stateParams.classRoomId);
      } else {
        $state.go("coursePay", { targetId : $scope.classRoom.id, targetType : 'classroom' });
      }
    };

    $scope.sign = function() {
      if ($scope.signInfo && $scope.signInfo.isSignedToday) {
        $scope.toast("今天已经签到了!");
        return;
      }
      ClassRoomService.sign({
        classRoomId : $stateParams.classRoomId
      }, function(data) {
        if(data.error) {
          $scope.toast(data.error.message);
          return;
        }

        $scope.signInfo = data;
      });
    }

    $scope.joinClassroom = function() {
      self.join(function() {
        self.goToPay();
      });
    }

    $scope.getTodaySignInfo = function() {
      ClassRoomService.getTodaySignInfo({
        classRoomId : $stateParams.classRoomId
      }, function(data) {
        $scope.signInfo = data;
      });
    };

    $scope.shardClassRoom = function() {
      var about = $scope.classRoom.about;

      cordovaUtil.share(
        app.host + "/classroom/" + $scope.classRoom.id, 
        $scope.classRoom.title, 
        self.filterContent(about, 20), 
        $scope.classRoom.largePicture
      );
    };

    $scope.learnByVip = function() {
      self.vipLeand($scope.classRoom.vipLevelId, function() {
        ClassRoomService.learnByVip({
          classRoomId : $stateParams.classRoomId
        }, function(data){
          if (! data.error) {
            window.location.reload();
          } else {
            $scope.toast(data.error.message);
          }
        }, function(error) {
          console.log(error);
        });
      });
    }
}

function ClassRoomCoursesController($scope, $stateParams, ClassRoomService, $state)
{
  var self = this;

  this.loadClassRoomCourses = function() {
    $scope.loading = true;
    ClassRoomService.getClassRoomCourses({
      classRoomId : $stateParams.classRoomId
    }, function(data) {
      $scope.loading = false;
      
      if (data.error) {
        $scope.toast(data.error.message);
        return;
      }
      $scope.courses = data.courses;
      $scope.progressArray = data.progress;
    });
  };

  this.loadClassRoomCourses();
}

function ClassRoomController($scope, $stateParams, ClassRoomService, AppUtil, $state, cordovaUtil, ClassRoomUtil)
{
  var self = this;

  this.loadClassRoom = function() {
    $scope.showLoad();
    ClassRoomService.getClassRoom({
      id : $stateParams.classRoomId
    }, function(data) {
      $scope.ratingArray = AppUtil.createArray(5);
      $scope.vipLevels = data.vipLevels;
      $scope.member = data.member;
      $scope.isFavorited = data.userFavorited;
      $scope.discount = data.discount;

      $scope.classRoomView = app.viewFloder + (data.member ? "view/classroom_learn.html" : "view/classroom_no_learn.html");
      $scope.classRoom = ClassRoomUtil.filterClassRoom(data.classRoom);
      $scope.hideLoad();
    });
  };

  $scope.loadClassRoomDetail = function() {
    $scope.classRoomDetailContent = $scope.classRoom.about;
    $scope.$apply();
  }

  $scope.loadReviews = function(){
      ClassRoomService.getReviews({
        classRoomId : $stateParams.classRoomId,
        limit : 1
      }, function(data) {
        $scope.reviews = data.data;
      });
  };

  $scope.loadStudents = function() {
    ClassRoomService.getStudents({
      classRoomId : $stateParams.classRoomId,
      limit : 3
    }, function(data) {
      $scope.students = data.resources;
    });
  };

  $scope.loadTeachers = function() {
    ClassRoomService.getTeachers({
      classRoomId : $stateParams.classRoomId,
    }, function(data) {
      if (data && data.length > 1) {
        var length = data.length;
        for (var i = 2; i < length; i++) {
          data.pop();
        };
      }

      $scope.classRoom.teachers = data;
    });
  };

  $scope.unLearn = function(reason) {
      $scope.showLoad();
      ClassRoomService.unLearn({
        reason : reason,
        targetType : "classroom",
        classRoomId : $stateParams.classRoomId
      }, function(data) {
        $scope.hideLoad();
        if (! data.error) {
          window.location.reload();
        } else {
          $scope.toast(data.error.message);
        }
      });
    }

  this.loadClassRoom();
};
app.controller('CourseListController', ['$scope', '$stateParams', '$state', 'CourseUtil', 'CourseService', 'CategoryService', CourseListController]);
function CourseListController($scope, $stateParams, $state, CourseUtil, CourseService, CategoryService)
{
    this.getTypeName = function(name, types) {

      var defaultName = "全部分类";
      if (!name || !types) {
        return defaultName;
      }

      for (var i = types.length - 1; i >= 0; i--) {
        if (name == types[i].type) {
          defaultName = types[i].name;
          break;
        }
      };

      return defaultName;
    }

    $scope.courseListSorts = CourseUtil.getCourseListSorts();
    $scope.courseListTypes = CourseUtil.getCourseListTypes();
    $scope.categoryTab = {
      category : "分类",
      type : this.getTypeName($stateParams.type, $scope.courseListTypes),
      sort : "综合排序",
    };

    $scope.canLoad = true;
    $scope.start = $scope.start || 0;

    console.log("CourseListController");
      $scope.loadMore = function(successCallback){
        if (! $scope.canLoad) {
            return;
        }
        setTimeout(function() {
            $scope.loadCourseList($stateParams.sort, successCallback);
        }, 200);
      };

      $scope.loadCourseList = function(sort, successCallback) {
             $scope.showLoad();
              CourseService.searchCourse({
                limit : 10,
                start: $scope.start,
                categoryId : $stateParams.categoryId,
                sort : sort,
                type : $stateParams.type
              }, function(data) {
                        $scope.hideLoad();
                        if (successCallback) {
                          successCallback();
                        }
                        var length  = data ? data.data.length : 0;
                        if (!data || length == 0 || length < 10) {
                            $scope.canLoad = false;
                        }

                        $scope.courses = $scope.courses || [];
                        for (var i = 0; i < length; i++) {
                          $scope.courses.push(data.data[i]);
                        };

                        $scope.start += data.limit;
              });
      }

      CategoryService.getCategorieTree(function(data) {
        $scope.categoryTree = data;
      });

      $scope.selectType = function(item) {
             $scope.$emit("closeTab", {});
             $scope.categoryTab.type = item.name;
             clearData();
             $stateParams.type  = item.type;
             setTimeout(function(){
                $scope.loadCourseList($scope.sort);
             }, 100);
      }

      function clearData() {
                $scope.canLoad = true;
                $scope.start = 0;
                $scope.courses = null;
      }

      $scope.selectSort = function(item) {
        $scope.$emit("closeTab", {});
        $scope.categoryTab.sort = item.name;
        $scope.sort = item.type;
        clearData();
        setTimeout(function(){
            $scope.loadCourseList(item.type);
         }, 100);
      }

      $scope.onRefresh = function() {
        clearData();
        $scope.loadCourseList($scope.sort);
      }

      $scope.categorySelectedListener = function(category) {
             $scope.$emit("closeTab", {});
             $scope.categoryTab.category = category.name;
             clearData();
             $stateParams.categoryId  =category.id;
             $scope.loadCourseList($scope.sort);
      }

      $scope.loadCourseList();
};
app.controller('CourseNoticeController', ['$scope', 'CourseService', 'ClassRoomService', '$stateParams', CourseNoticeController]);

function CourseNoticeController($scope, CourseService, ClassRoomService, $stateParams)
{
	var limit = 10;
	$scope.start = 0;
	$scope.showLoadMore = false;
	
	this.loadCourseNotices = function(callback) {
	    CourseService.getCourseNotices({
	      start : $scope.start,
	      limit : limit,
	      courseId : $stateParams.targetId
	    }, callback);
	};

  	this.loadClassRoomNotices = function(callback) {
	    ClassRoomService.getAnnouncements({
	      start : $scope.start,
	      limit : 10,
	      classRoomId : $stateParams.targetId
	    }, callback);
	};

  	this.initTargetService = function(targetType) {
	    if (targetType == "course") {
	    	$scope.titleType = "课程";
	      	self.targetService = this.loadCourseNotices;
	    } else if (targetType == "classroom") {
	    	$scope.titleType = "班级";
	      	self.targetService = this.loadClassRoomNotices;
	    }
	};


	function loadNotices(start, limit) {
		$scope.showLoad();
		self.targetService(function(data) {
			$scope.hideLoad();
			$scope.notices = $scope.notices || [];
			
			if (! data || data.length < 10) {
				$scope.showLoadMore = false;
				return;
			}

			$scope.showLoadMore = true;
			for (var i = 0; i < data.length; i++) {
                $scope.notices.push(data[i]);
           	};
			$scope.start += limit;
		});
	}

	$scope.loadMore = function() {
		loadNotices($scope.start, limit);
	}

	this.initTargetService($stateParams.targetType);
};
app.controller('FoundCourseController', ['$scope', 'SchoolService', '$state', FoundCourseController]);

function FoundCourseController($scope, SchoolService, $state)
{
	console.log("FoundCourseController");
	SchoolService.getSchoolBanner(function(data) {
		$scope.banners = data;
	});

	SchoolService.getRecommendCourses({ limit : 3 }, function(data) {
		$scope.recommedCourses = data.data;
	});

	SchoolService.getLatestCourses({ limit : 3 }, function(data) {
		$scope.latestCourses = data.data;
	});
}

app.controller('FoundLiveController', ['$scope', 'SchoolService', FoundLiveController]);

function FoundLiveController($scope, SchoolService)
{
	console.log("FoundLiveController");

	SchoolService.getLiveRecommendCourses({ limit : 3 } , function(data) {
		$scope.liveRecommedCourses = data.data;
	});

	SchoolService.getLiveLatestCourses( {  limit : 3,  },  function(data) {
		$scope.liveLatestCourses = data.data;
	});
}

app.controller('FoundClassRoomController', ['$scope', 'ClassRoomService', 'ClassRoomUtil', FoundClassRoomController]);

function FoundClassRoomController($scope, ClassRoomService, ClassRoomUtil)
{
	console.log("FoundClassRoomController");

  	ClassRoomService.getRecommendClassRooms({ limit : 3 }, function(data) {
  		$scope.recommendClassRooms = ClassRoomUtil.filterClassRooms(data.data);
  	});

  	ClassRoomService.getLatestClassrooms({ limit : 3 }, function(data) {
  		$scope.latestClassrooms = ClassRoomUtil.filterClassRooms(data.data);
  	});


};
app.controller('HomeWorkController', ['$scope', '$stateParams', 'HomeworkManagerService', 'AppUtil', HomeworkCheckController]);

function HomeworkCheckController($scope, $stateParams, HomeworkManagerService, AppUtil)
{
	function uncertainChoiceType(item) {
		return new choiceType(item);

	}

	function choiceType(item) {
		var self = this;

		this.getResultAnswer = function() {
			if (item.result && item.result.length > 0) {
				var answer = item.result.answer;
				return self.coverAnswer(answer);
			}

			return "未回答";
		}

		this.getIndexType = function(index) {
			switch (index) {
					case 0:
						return "A";
					case 1:
						return "B";
					case 2:
						return "C";
					case 3:
						return "D";
					case 4:
						return "E";
					case 5:
						return "F";
					case 6:
						return "G";
					case 7:
						return "H";
					case 8:
						return "I";
					case 9:
						return "J";
				}

				return "";
		};

		this.coverAnswer = function(answer) {
			var answerResult = "";
			for (var i = 0; i < answer.length; i++) {
				answerResult += self.getIndexType(i) + ",";
			};

			return answerResult;
		}

		this.getAnswer = function() {
			if (item.answer && item.answer.length > 0) {
				return self.coverAnswer(item.answer);
			}

			return "未回答";	
		};

		return {
			getAnswer : this.getAnswer,
			getIndexType : this.getIndexType,
			getResultAnswer : this.getResultAnswer,
		};
	};

	function essayType(item) {
		var self = this;
		this.getAnswer = function() {
			if (!item.answer || item.answer.length == 0) {
				return "";
			}

			return item.answer[0];	
		};

		this.getResultAnswer = function() {
			if (item.result) {
				var answer = item.result.answer;
				return self.getAnswer(answer);
			}

			return "no";
		}

		return {
			getAnswer : this.getAnswer,
			getResultAnswer : this.getResultAnswer
		};
	};

	var questionType = {
		single_choice : choiceType,
		essay : essayType,
		uncertain_choice : choiceType
	};

	$scope.loadHomeworkResult = function() {
		$scope.showLoad();
		HomeworkManagerService.showCheck({
			homeworkResultId : $stateParams.homeworkResultId
		}, function(data) {
			$scope.hideLoad();
			$scope.homeworkResult = data;
			$scope.items = data.items;
			$scope.currentQuestionIndex = 1;
			console.log(data);
		});
	};

	$scope.getResultAnswer = function(item) {
		var type = questionType[item.type];
		return type(item).getResultAnswer();
	};

	$scope.getItemAnswer = function(item) {
		var type = questionType[item.type];
		return type(item).getAnswer();
	}

	$scope.getItemStem = function(index, type) {
		var typeStr = "";
		switch (type) {
			case "single_choice":
				typeStr = "单选题";
				break;
			case "determine":
				typeStr = "判断题";
				break;
			case "essay":
				typeStr = "问答题";
				break;
			case "fill":
				typeStr = "填空题";
				break;
			case "material":
				typeStr = "材料题";
				break;
			case "uncertain_choice":
				typeStr = "不定项题";
				break;
			case "choice":
				typeStr = "多选题";
		}

		return AppUtil.formatString("%1, (%2)", index + 1, typeStr);
	}

	$scope.questionItemChange = function() {
		$scope.$apply(function() {
			$scope.currentQuestionIndex = $scope.scrollIndex + 1;
		});
	}

	$scope.getItemView = function(item) {
		var type = item.type;
		if (type.indexOf('choice')!= -1) {
			type = "choice";
		}
		return "view/homework_" + type + "_view.html";
	}

	$scope.getItemIndex = function(item, index) {
		var type = questionType[item.type];
		return type(item).getIndexType(index);
	}

	$scope.getFillQuestionItem = function(item) {
		var items = [], answer = item.answer;
		for (var i = 0; i < answer.length; i++) {
			items[i] = AppUtil.formatString("填写空(%1)答案", i + 1);
		};

		return items;
	}
};
app.controller('LessonController', ['$scope', '$stateParams', 'LessonService', 'cordovaUtil', LessonController]);

function LessonController($scope, $stateParams, LessonService, cordovaUtil)
{	
	var self = this;

	self.loadLesson = function() {
		LessonService.getLesson({
			courseId : $stateParams.courseId,
			lessonId : $stateParams.lessonId
		},function(data) {
			if (data.error) {
				$scope.toast(data.error.message);
				return;
			}
      
			var lesson = data;
      if (!lesson) {
        alert("请先加入学习");
        cordovaUtil.closeWebView();
        return;
      }

      if (lesson.type == "flash" || "qqvideo" == lesson.mediaSource) {
        alert("客户端暂不支持该课时类型，敬请期待新版");
        cordovaUtil.closeWebView();
        return;
      }
      cordovaUtil.learnCourseLesson(lesson.courseId, lesson.id, []);  
      cordovaUtil.closeWebView();
		});
	}

	this.loadLesson();
}

app.controller('CourseLessonController', ['$scope', '$stateParams', 'LessonService', '$state', 'cordovaUtil', CourseLessonController]);
function CourseLessonController($scope, $stateParams, LessonService, $state, cordovaUtil)
{

  var self = this;
  $scope.loading = true;
  this.loadLessons = function() {
      LessonService.getCourseLessons({
        courseId : $stateParams.courseId,
        token : $scope.token
      }, function(data) {
        $scope.loading = false;
        $scope.lessons = data.lessons;
        $scope.learnStatuses = data.learnStatuses;

        for( index in data.learnStatuses ) {
            $scope.lastLearnStatusIndex = index;
        }

        self.continueLearnLesson();
      });
    }

    this.continueLearnLesson = function() {
      $scope.$root.$on("continueLearnCourse", function(event, data) {
          if (! $scope.lastLearnStatusIndex) {
            alert("还没有开始学习!");
            return
          }
          
          var continueLesson =  self.findLessonById($scope.lastLearnStatusIndex);
          if (! continueLesson) {
            alert("还没有开始学习");
            return
          }
          $scope.learnLesson(continueLesson);
        });
    };

    this.findLessonById = function(lessonId) {
      var lessons = $scope.lessons;
      for (var i = 0; i < lessons.length; i++) {
        if (lessonId == lessons[i].id) {
          return lessons[i];
        }
      };

      return null;
    };

    this.createLessonIds = function() {
      var index = 0;
      var lessonIds = [];
      var lessons = $scope.lessons;
      for (var i = 0; i < lessons.length; i++) {
        if ("lesson" == lessons[i].itemType) {
          lessonIds[index++] = lessons[i].id;
        }
      };

      return lessonIds;
    };

    //lesson.free=1 is free
    $scope.learnLesson = function(lesson) {
      if (!$scope.user) {
        var dia = $.dialog({
                title : '课程提醒' ,
                content : '你还未登录网校',
                button : [ "登录网校" ]
        });

        dia.on("dialog:action",function(e){
            cordovaUtil.openWebView(app.rootPath + "#/login/course");
        });
        return;
      }
      if (! $scope.member && 1 != lesson.free) {
        alert("请先加入学习");
        return;
      }

      if ("lesson" != lesson.itemType) {
        return;
      }

      if (lesson.type == "flash" || "qqvideo" == lesson.mediaSource) {
        alert("客户端暂不支持该课时类型，敬请期待新版");
        return;
      }
      cordovaUtil.learnCourseLesson(lesson.courseId, lesson.id, self.createLessonIds());     
    }

    $scope.getLessonBoxStyle = function($index) {

      var style = "";
      $prevItem = $scope.lessons[$index -1];
      $nextItem = $scope.lessons[$index +1];

      var isNoTop = false, isNoBottom = false;
      if (!$prevItem || 'chapter' == $prevItem.itemType) {
        style += " no-top";
        isNoTop = true;
      }

      if (! $nextItem || 'lesson' != $nextItem.itemType) {
        style += " no-bottom";
        isNoBottom = true;
      }

      if (isNoTop && isNoBottom) {
        style = "hidden";
      }

      return style;
    }

    this.loadLessons();
    $scope.$on("$destroy", function(event, data) {
      console.log(11);
        $scope.$root.$on("continueLearnCourse", null);
    });
};
app.controller('LoginController', ['$scope', 'UserService', '$stateParams', 'platformUtil', 'cordovaUtil', '$state', LoginController]);

function LoginController($scope, UserService, $stateParams, platformUtil, cordovaUtil, $state, $q)
{	
	console.log("LoginController");

	$scope.user = {
		username : null,
		password : null
	};

	cordovaUtil.getThirdConfig($q).then(function(data) {
		$scope.thirdConfig = data;
	});

	$scope.jumpToMain = function() {
		$state.go("slideView.mainTab");
	}

	$scope.getThirdStyle = function() {
		if (!$scope.thirdConfig || $scope.thirdConfig.length <= 1) {
			return "";
		}
		return $scope.thirdConfig.length == 2 ? "ui-grid-halve" : "ui-grid-trisect";
	}

	$scope.hasThirdType = function(name) {
		if (! $scope.thirdConfig) {
			return false;
		}

		return $scope.thirdConfig.indexOf(name) != -1;
	}

	$scope.login = function(user) {
		$scope.showLoad();
		UserService.login({
			"_username": user.username,
		"_password" : user.password
		}, function(data) {
			
		$scope.hideLoad();
			if (data.error) {
			$scope.toast(data.error.message);
			return;
		}

		if (platformUtil.native) {
			esNativeCore.closeWebView();
			return;
		}

		if ($stateParams.goto) {
			window.history.back();
		} else {
			$scope.jumpToMain();
		}
		
		});
	}

	$scope.loginWithOpen = function(type) {
		cordovaUtil.openPlatformLogin(type);
	}

	$scope.jumpToSetting = function() {
		cordovaUtil.startAppView("setting", {});
	}
};
function MainTabController($scope, sideDelegate, $state) {
    console.log("MainTabController");
}

app.controller('FoundTabController', ['$scope', 'CategoryService', 'AppUtil', 'sideDelegate', '$state', FoundTabController]);

function FoundTabController($scope, CategoryService, AppUtil, cordovaUtil, $state) {
    console.log("FoundTabController");
    $scope.toggleView = function(view) {
        $state.go("slideView.mainTab." + view);
    };

    $scope.toggle = function() {
        
        if ($scope.platform.native) {
            return;
        }

        cordovaUtil.openDrawer("open");
    };

    $scope.categorySelectedListener = function(category) {
        cordovaUtil.openWebView(app.rootPath + "#/courselist/" + category.id);
        $(".ui-dialog").dialog("hide");
    };

    CategoryService.getCategorieTree(function(data) {
        $scope.categoryTree = data;
        $scope.openModal = function($event) {
            var dialog = $(".ui-dialog");
            dialog.dialog("show");
            $(".ui-dialog-bg").click(function(e) {
                dialog.dialog("hide");
            });
        };
    });

    var self = this;
    this.parseBannerAction = function(action) {
        this.courseAction = function(params) {
            cordovaUtil.openWebView(app.rootPath + "#/course/" + params);
        }

        this.webviewAction = function(params) {
            cordovaUtil.openWebView(params);
        }

        this.noneAction = function() {}

        return this[action + "Action"];
    }

    $scope.bannerClick = function(banner) {
        var bannerAction = self.parseBannerAction(banner.action);
        bannerAction(banner.params);
    }

    $scope.loadPage = function(pageName) {
        $scope[pageName] = 'view/found_' + pageName + '.html';
        $scope.$apply();
    }
}


app.controller('MessageTabController', ['$scope', FoundTabController]);

function MessageTabController($scope) {
    console.log("MessageTabController");
}

app.controller('ContactTabController', ['$scope', ContactTabController]);

function ContactTabController($scope) {
    console.log("ContactTabController");
}
;
app.controller('MyFavoriteCourseController', ['$scope', 'CourseService', 'CourseUtil', MyFavoriteCourseController]);
app.controller('MyFavoriteLiveController', ['$scope', 'CourseService', 'CourseUtil', MyFavoriteLiveController]);

function MyFavoriteBaseController($scope, CourseService, CourseUtil)
{
  var self = this;
  $scope.data  = CourseUtil.getFavoriteListTypes();

    this.loadDataList = function(type) {
      $scope.showLoad();
      var content = $scope.data[type];
      CourseService.getFavoriteCourse(
        content.url,
        {
          limit : 100,
        start: content.start
      }, function(data) {
            $scope.hideLoad();
            if (!data || data.data.length == 0) {
              content.canLoad = false;
            }

            content.data = content.data || [];
            content.data = content.data.concat(data.data);
            content.start += data.limit;

            if (data.total && content.start >= data.total) {
              content.canLoad = false;
            }
        }
      );
    }
}

function MyFavoriteCourseController($scope, CourseService, CourseUtil)
{
      console.log("MyFavoriteCourseController");
	this.__proto__ = new MyFavoriteBaseController($scope, CourseService, CourseUtil);

      var self = this;
      this.loadCourses = function() {
        self.loadDataList("course");
      }

      this.loadCourses();
}

function MyFavoriteLiveController($scope, CourseService, CourseUtil)
{
      console.log("MyFavoriteLiveController");
      this.__proto__ = new MyFavoriteBaseController($scope, CourseService, CourseUtil);

      var self = this;
      this.loadLiveCourses = function() {
        self.loadDataList("live");
      }

      this.loadLiveCourses();
};
function MyGroupBaseController($scope, serviceCallBack) {

  var self = this;
  this.limit = 10;
  $scope.data = [];
  $scope.canLoad = true;
  $scope.start = $scope.start || 0;

  this.loadDataList = function(type) {
      serviceCallBack({
        limit : self.limit,
        start: $scope.start,
        type : type
      }, function(data) {
        
        var length  = data ? data.data.length : 0;
        if (!data || length == 0 || length < self.limit) {
            $scope.canLoad = false;
          }

          $scope.data = $scope.data.concat(data.data);
          $scope.start += self.limit;
      });
    }
}

function MyGroupNoteController($scope, NoteService, cordovaUtil, $state)
{
      console.log("MyGroupNoteController");
      var self = this;
      this.__proto__ = new MyGroupBaseController($scope, NoteService.getNoteList);

    $scope.canLoadMore = function() {
      return $scope.canLoad;
    };

    $scope.loadMore = function(){
      self.loadDataList();
    };

     this.loadDataList();
}

function MyGroupQuestionController($scope, QuestionService)
{
  console.log("MyGroupQuestionController");
      this.__proto__ = new MyGroupBaseController($scope, QuestionService.getCourseThreads);
  
    $scope.canLoadMore = function() {
      return $scope.canLoad;
    };

    $scope.loadMore = function(){
      self.loadDataList("question");
    };

     this.loadDataList("question");
}

function MyGroupThreadController($scope, QuestionService)
{
  console.log("MyGroupThreadController");
  this.__proto__ = new MyGroupBaseController($scope, QuestionService.getCourseThreads);

    $scope.canLoadMore = function() {
      return $scope.canLoad;
    };

    $scope.loadMore = function(){
      self.loadDataList("discussion");
    };

   this.loadDataList("discussion");
}

app.controller('MyGroupQuestionController', ['$scope', 'QuestionService', MyGroupQuestionController]);
app.controller('MyGroupNoteController', ['$scope', 'NoteService', 'cordovaUtil', '$state', MyGroupNoteController]);
app.controller('MyGroupThreadController', ['$scope', 'QuestionService', MyGroupThreadController]);;
app.controller('MyLearnController', ['$scope', 'CourseService', 'ClassRoomService', MyLearnController]);

function MyLearnController($scope, CourseService, ClassRoomService)
{
	var self = this;
	self.content = {
		course : {
			start : 0,
			canLoad : true,
			data : undefined
		},
		live : {
			start : 0,
			canLoad : true,
			data : undefined
		},
    classroom : {
      start : 0,
      canLoad : true,
      data : undefined
    }
	};

	$scope.course = self.content.course;
	$scope.live = self.content.live;
  $scope.classroom = self.content.classroom

  	self.loadDataList = function(content, serviceCallback, successCallback) {
      $scope.showLoad();
  		serviceCallback({
  			limit : 10,
			start: content.start
  		}, function(data) {

        $scope.hideLoad();
        if (successCallback) {
          successCallback();
        }
  			if (!data || data.data.length == 0) {
    			content.canLoad = false;
    		}

    		content.data = content.data || [];
    		content.data = content.data.concat(data.data);
    		content.start += data.limit;

    		if (data.limit > data.data.length) {
    			content.canLoad = false;
    		}
    		if (data.total && content.start >= data.total) {
    			content.canLoad = false;
    		}
  		});
  	}

  	$scope.canLoadMore = function(type) {
  		return self.content[type].canLoad;
  	};

  	$scope.loadMore = function(type, successCallback){
  		switch (type) {
  			case "course": 
  				self.loadDataList(self.content.course, CourseService.getLearningCourse, successCallback);
  				break;
  			case "live": 
  				self.loadDataList(self.content.live, CourseService.getLiveCourses, successCallback);
  				break;
        case "classroom":
          self.loadDataList(self.content.classroom, ClassRoomService.getLearnClassRooms, successCallback);
          break;
  		}
  	};

  	$scope.loadCourses = function() {
  		self.loadDataList(self.content.course, CourseService.getLearningCourse);
  	}

  	$scope.loadLiveCourses = function() {
  		self.loadDataList(self.content.live, CourseService.getLiveCourses);
  	}

    $scope.loadClassRooms = function() {
      self.loadDataList(self.content.classroom, ClassRoomService.getLearnClassRooms);
    }

  	$scope.loadCourses();
};
app.controller('CourseCouponController', ['$scope', 'CouponService', '$stateParams', '$window', CourseCouponController]);
app.controller('VipListController', ['$scope', '$stateParams', 'SchoolService', 'cordovaUtil', VipListController]);
app.controller('VipPayController', ['$scope', '$stateParams', 'SchoolService', 'VipUtil', 'OrderService', 'cordovaUtil', 'platformUtil', VipPayController]);

function BasePayController($scope, $stateParams, OrderService, cordovaUtil, platformUtil)
{
	var self = this;
	$scope.priceType = "RMB";
	$scope.payMode = "alipay";

	this.showPayResultDlg = function() {
		var dia = $.dialog({
		        title : '确认支付' ,
		        content : '是否支付完成?' ,
		        button : [ "确认" ,"取消" ]
		});

		dia.on("dialog:action",function(e){
		        if (e.index == 0) {
		        	window.history.back();
		        }
		});
	}

	this.initPayMode = function(data) {
		$scope.coin = data.coin;
		if (data.coin && data.coin.priceType) {
			$scope.priceType = data.coin.priceType;
			$scope.payMode = ($scope.checkIsCoinMode() || "Coin" == $scope.priceType) ? "coin" : "alipay";
		}
		$scope.orderLabel = self.getOrderLabel($stateParams.targetType);
	}

	$scope.checkIsCoinMode = function() {
		return false;
	}

	$scope.changePayMode = function() {
		if ("Coin" == $scope.priceType) {
			return;
		}

		if ($scope.payMode == "coin") {
			$scope.payMode = "alipay";
		} else {
			$scope.payMode = "coin";
		}

		self.changePrice($scope.payMode);
	}

	this.showErrorResultDlg = function(error) {
		if ("coin_no_enough" == error.name) {
			var dia = $.dialog({
			        title : '支付提醒' ,
			        content : '账户余额不足!' ,
			        button : [ "确认" ,"充值" ]
			});

			dia.on("dialog:action",function(e){
			        if (e.index == 1) {
			        	cordovaUtil.startAppView("rechargeCoin", null);
			        }
			});
			return;
		}
		$scope.toast(error.message);
	}

	this.getOrderLabel  = function(type) {
		switch(type) {
			case 'course':
				return "购买课程";
			case 'vip':
				return "购买会员";
			case 'classroom':
				return "购买班级";
		}

		return "";
	}

	this.payOrder = function(price, params, payPassword) {

		var payment = $scope.payMode;
		var defaultParams = {
			payment : payment,
			payPassword : payPassword ? payPassword : "",
			totalPrice : price,
			couponCode : $scope.formData ? $scope.formData.code : "",
			targetType : $stateParams.targetType,
			targetId : $stateParams.targetId
		};

		for(var i in params) {
			defaultParams[i] = params[i];
		}

		OrderService.createOrder(defaultParams, function(data) {
			if (data.status != "ok") {
				self.showErrorResultDlg({
					name : "error",
					message : data.message
				});
				return;
			}

			if (data.paid == true) {
				window.history.back();
			} else if (data.payUrl != "") {
				cordovaUtil.pay($scope.orderLabel, data.payUrl);
				self.showPayResultDlg();
			}
		});
	};

	this.submitToPay = function(price, params) {
		if ($scope.payMode == "coin") {
			cordovaUtil.showInput("支付提醒", "请输入支付密码", "password", function(input) {
				if (!input || input.length == 0) {
					alert("请输入支付密码!");
					return;
				}
				self.payOrder(price, params, input);
			});
			return;
		}

		self.payOrder(price, params);
	}
}

function VipPayController($scope, $stateParams, SchoolService, VipUtil, OrderService, cordovaUtil, platformUtil)
{
	var self = this;
	$stateParams.targetType = "vip";
	$stateParams.targetId = $stateParams.levelId;
	this.__proto__ = new BasePayController($scope, $stateParams, OrderService, cordovaUtil, platformUtil);
	
	$scope.loadPayOrder = function() {
		$scope.showLoad();
		OrderService.getPayOrder({
			targetType : 'vip',
			targetId : $stateParams.levelId
		}, function(data) {
			$scope.data = data.orderInfo;
			self.initPayMode(data);

			$scope.payModes = VipUtil.getPayMode(data.orderInfo.buyType);
			$scope.selectedNum = 1;
			$scope.selectedPayMode = $scope.payModes[0];

			self.changePrice($scope.payMode);
			$scope.totalPayPrice = self.sumTotalPirce();
			$scope.initPopver();
			$scope.hideLoad();
		});
	}
	
	this.changePrice = function(payMode) {
		var price = self.sumTotalPirce();
		if ($scope.coin && "Coin" != $scope.priceType && payMode == "coin") {
			price = price * $scope.coin.cashRate;
		}
		var couponPrice = $scope.coupon ? $scope.coupon.decreaseAmount : 0;
		$scope.totalPayPrice = price > couponPrice ? price - couponPrice : 0;
	}

	$scope.changePayMode = function() {
		if ("Coin" == $scope.priceType) {
			return;
		}

		if ($scope.payMode == "coin") {
			$scope.payMode = "alipay";
		} else {
			$scope.payMode = "coin";
		}

		self.changePrice($scope.payMode);
	}

	this.sumTotalPirce = function() {
		var level = $scope.data.level;
		var payTypes = VipUtil.getPayType();

		var price = $scope.selectedPayMode.type == payTypes.byMonth ? level.monthPrice : level.yearPrice;
		var totalPayPrice = $scope.selectedNum * price;
		return totalPayPrice;
	}

	$scope.add = function() {
		if ($scope.selectedNum < 12) {
			$scope.selectedNum ++;
			$scope.totalPayPrice = self.sumTotalPirce();
			self.changePrice($scope.payMode);
		}
	}

	$scope.sub = function() {
		if ($scope.selectedNum > 1) {
			$scope.selectedNum --;
			$scope.totalPayPrice = self.sumTotalPirce();
			self.changePrice($scope.payMode);
		}
	}

	$scope.initPopver = function() {

		  $scope.showPopover = function($event) {
		  	$scope.isShowPayMode = ! $scope.isShowPayMode ;
		  };

		  $scope.selectPayMode = function(payMode) {
		  	$scope.selectedPayMode = payMode;
			$scope.totalPayPrice = self.sumTotalPirce();
		  	$scope.isShowPayMode = false;
		  }
	}

	$scope.pay = function() {
		self.submitToPay($scope.totalPayPrice, {
			duration : $scope.selectedNum,
			unitType : $scope.selectedPayMode.name
		});
	}

	$scope.payVip = function() {
		OrderService.payVip({
			targetId : $stateParams.levelId,
			duration : $scope.selectedNum,
			unitType : $scope.selectedPayMode.name
		}, function(data) {
			if (data.status == "ok" && data.payUrl != "") {
				cordovaUtil.pay("支付会员", data.payUrl);
				self.showPayResultDlg();
			} else if (data.error) {
				$scope.toast(data.error.message);
			}
		});
	}

}

function VipListController($scope, $stateParams, SchoolService, cordovaUtil)
{
	var user = null;
	
	$scope.loadVipList = function() {
		$scope.showLoad();
		SchoolService.getSchoolVipList({
			userId : $scope.user.id
		}, function(data) {
			$scope.hideLoad();
			if (! data || !data.vips || data.vips.length == 0) {
				var dia = $.dialog({
			        title : '会员提醒' ,
			        content : '网校尚未开启Vip服务!' ,
			        button : [ "退出" ]
				});

				dia.on("dialog:action",function(e){
					cordovaUtil.closeWebView();
				});
			}
			$scope.data = data;
			user = data.user;
		});
	}

	$scope.getVipName = function() {
		if (!$scope.data) {
			return "";
		}

		if (!user || !user.vip) {
			return "暂时还不是会员";
		}
		var levelId = user.vip.levelId;
		var vips = $scope.data.vips;
		if (levelId <= 0) {
			return "暂时还不是会员";
		}
		for (var i = 0; i < vips.length; i++) {
			if (levelId == vips[i].id) {
				return vips[i].name;
			}
		};

		return "暂时还不是会员";
	}
}

function CourseCouponController($scope, CouponService, $stateParams, $window)
{	
	$scope.formData = { code : "" };
	$scope.checkCoupon = function() {
		$scope.formData.error = "";
		$scope.showLoad();
		CouponService.checkCoupon({
			courseId : $stateParams.courseId,
			type : "course",
			code : $scope.formData.code
		}, function(data) {
			$scope.hideLoad();
			if (data.meta.code != 200) {
				$scope.formData.error = data.meta.message;
				return;
			}
			$window.history.back();
			$scope.$emit("coupon", { coupon : data.data });
		}, function(data) {
			$scope.hideLoad();
			$scope.toast("检验优惠码错误");
		});
	}
}

app.controller('CoursePayController', ['$scope', '$stateParams', 'OrderService', 'CouponService', 'AppUtil', 'cordovaUtil', 'platformUtil', CoursePayController]);
function CoursePayController($scope, $stateParams, OrderService, CouponService, AppUtil, cordovaUtil, platformUtil)
{	
	var self = this;
	this.__proto__ = new BasePayController($scope, $stateParams, OrderService, cordovaUtil, platformUtil);

	this.loadOrder = function() {
		OrderService.getPayOrder({
			targetType : $stateParams.targetType,
			targetId : $stateParams.targetId
		}, function(data) {
			$scope.data = data;
			self.initPayMode(data);
			self.changePrice($scope.payMode);
		});
	};

	$scope.$parent.$on("coupon", function(event, data) {
		$scope.coupon = data.coupon;
	});

	this.changePrice = function(payMode) {
		var price = $scope.data.orderInfo.price;
		if ($scope.coin && "Coin" != $scope.priceType && payMode == "coin") {
			price = price * $scope.coin.cashRate;
		}
		var couponPrice = $scope.coupon ? $scope.coupon.decreaseAmount : 0;
		$scope.payPrice = price > couponPrice ? price - couponPrice : 0;
	}

	$scope.selectCoupon = function() {
		$scope.formData = { code : "", error : '' };
		self.dialog = $(".ui-dialog");
		self.dialog.dialog("show");
	}

	$scope.changePayMode = function() {
		if ("Coin" == $scope.priceType) {
			return;
		}

		if ($scope.payMode == "coin") {
			$scope.payMode = "alipay";
		} else {
			$scope.payMode = "coin";
		}

		self.changePrice($scope.payMode);
	}

	$scope.pay = function() {
		self.submitToPay($scope.data.orderInfo.price, null);
	}

	$scope.checkCoupon = function() {
		if ($scope.formData.code.length <= 0) {
			alert("请输入优惠码");
			return;
		}

		$scope.showLoad();
		CouponService.checkCoupon({
			courseId : $stateParams.courseId,
			type : "course",
			code : $scope.formData.code
		}, function(data) {
			$scope.hideLoad();
			if (data.error) {
				$scope.formData.error = data.error.message;
				return;
			}
			$scope.$emit("coupon", { coupon : data });
			$scope.close();

		}, function(data) {
			$scope.hideLoad();
			$scope.toast("检验优惠码错误");
		});
	}

	$scope.close = function() {
		self.dialog.dialog("hide");
	}

	$scope.isShowCoupon = function() {
		if (platformUtil.native && (platformUtil.iPhone || platformUtil.iPad)) {
			return false;
		}
		if ($scope.data && $scope.data.isInstalledCoupon) {
			return true;
		}
		return false;
	};

	self.loadOrder();
}
;
app.controller('QuestionController', ['$scope', 'QuestionService', '$stateParams', QuestionController]);
app.controller('NoteController', ['$scope', 'NoteService', '$stateParams', NoteController]);

function QuestionController($scope, QuestionService, $stateParams)
{	
	var self = this;
	this.loadQuestion = function() {
		$scope.showLoad();
		QuestionService.getThread({
			courseId: $stateParams.courseId,
			threadId : $stateParams.threadId
		}, function(data) {
			$scope.thread = data;
			$scope.hideLoad();

			self.loadTeacherPost();
			self.loadTheadPost();
		});
	}
	
	this.loadTeacherPost = function() {
		QuestionService.getThreadTeacherPost({
			courseId: $stateParams.courseId,
			threadId : $stateParams.threadId
		}, function(data) {
			$scope.teacherPosts = data;
		});
	}

	this.loadTheadPost = function() {
		QuestionService.getThreadPost({
			courseId: $stateParams.courseId,
			threadId : $stateParams.threadId
		}, function(data) {
			$scope.threadPosts = data.data;
		});
	}

	self.loadQuestion();
}

function NoteController($scope, NoteService, $stateParams)
{	
	var self = this;
	this.loadNote = function() {
		$scope.showLoad();
		NoteService.getNote({
			noteId: $stateParams.noteId
		}, function(data) {
			$scope.note = data;
			$scope.hideLoad();
		});
	}

	self.loadNote();
};
app.controller('RegistController', ['$scope', 'platformUtil', 'UserService', '$state', RegistController]);

function RegistController($scope, platformUtil, UserService, $state)
{
	console.log("RegistController");

	$scope.user = {
		phone : null,
		code : null,
		password: null
	};

	var self = this;

	this.registHandler = function(params) {
		$scope.showLoad();
		UserService.regist(params, function(data) {
			$scope.hideLoad();
			if (data.error) {
				$scope.toast(data.error.message);
				return;
			}
			if (platformUtil.native) {
				esNativeCore.closeWebView();
				return;
			}
			self.jumpToMain();
		}, function(error) {
			$scope.toast("注册失败");
		});
	}

	this.jumpToMain = function() {
		$state.go("slideView.mainTab");
	}

	$scope.checkCode = function(code) {
		return !code || code.length == 0;
	};

	$scope.sendSmsCode = function(phone) {
		if (!parseInt(phone)) {
			alert("手机格式不正确!");
			return;
		}

		UserService.smsSend({
			phoneNumber : phone
		}, function(data) {
			$scope.toast(data.error.message);
		});
	}

	$scope.registWithPhone = function(user) {
		if (!parseInt(user.phone)) {
			alert("手机格式不正确!");
			return;
		}
		if (user.password && (user.password.length < 5 || user.password.length > 20)) {
			alert("密码格式不正确!");
			return;
		}

		if ($scope.checkCode(user.code)) {
			alert("验证码不正确!");
			return;
		}
		self.registHandler({
			phone : user.phone,
			smsCode : user.code,
			password : user.password,
		});
	}

	$scope.registWithEmail = function(user) {
		if (!user.email) {
			alert("邮箱格式不正确!");
			return;
		}
		if (user.password && (user.password.length < 5 || user.password.length > 20)) {
			alert("密码格式不正确!");
			return;
		}
		self.registHandler({
			email : user.email,
			password : user.password,
		});
	}
};
app.controller('SearchController', ['$scope', 'CourseService', 'ClassRoomService', 'cordovaUtil', '$timeout', SearchController]);

function SearchController($scope, CourseService, ClassRoomService, cordovaUtil, $timeout)
{
	$scope.search = "";
	var self = this;
	
	$scope.focusSearchInput = function() {
		$('.ui-searchbar-wrap').addClass('focus');
        		$('.ui-searchbar-input input').focus();
        		esNativeCore.showKeyInput();
	};

	$scope.inputKeyPress = function($event) {
		if ($event.keyCode == 13 && $scope.search.length > 0) {
			self.search();
		}
	};

	$scope.seach = function() {
		if ($scope.search.length == 0) {
			cordovaUtil.closeWebView();
			return;
		}
		self.search();
	};

	this.initSearch = function() {
		self.content = {
			course : {
				start : 0,
				canLoad : true,
				total : 0,
				data : undefined
			},
	    classroom : {
	      start : 0,
	      canLoad : true,
	      total : 0,
	      data : undefined
	    }
		};

		$scope.searchCourse = self.content.course;
		$scope.searchClassRoom = self.content.classroom;
	};

	this.search = function() {
		$scope.showLoad();
		self.initSearch();
		self.loadSearchCourses(self.content.course);
		self.loadSearchClassrooms(self.content.classroom);
	};

	this.loadSearchCourses = function(content) {
          CourseService.searchCourse({
            limit : 5,
            start: content.start,
            search : $scope.search
          }, function(data) {
                    $scope.hideLoad();
                    var length  = data ? data.data.length : 0;
                    content.canLoad = ! (! data || length < 10);

                    content.data = content.data || [];
                    for (var i = 0; i < length; i++) {
                      content.data.push(data.data[i]);
                    };

                    content.total = data.total;
                    content.start += data.limit;
                    $scope.searchCourse = content;
                    $scope.$apply();
          });
  };

  this.loadSearchClassrooms = function(content) {
  	ClassRoomService.search({
  		limit : 5,
  		start : content.start,
  		title : $scope.search
  	}, function(data) {
  		$scope.hideLoad();
      var length  = data ? data.data.length : 0;
      content.canLoad = ! (! data || length < 10);

      content.data = content.data || [];
      for (var i = 0; i < length; i++) {
        content.data.push(data.data[i]);
      };

      content.total = data.total;
      content.start += data.limit;
      $scope.searchClassRoom = content;
      $scope.$apply();
  	});
  }
};
app.controller('SettingController', ['$scope', 'UserService', '$state', SettingController]);

function SettingController($scope, UserService, $state)
{
	$scope.isShowLogoutBtn = $scope.user ? true : false;
	$scope.logout = function() {
		$scope.showLoad();
		UserService.logout( {}, function(data) {
			$scope.hideLoad();
			$state.go("slideView.mainTab");
		});
	}
};
app.controller('MyInfoController', ['$scope', 'UserService', 'cordovaUtil', 'platformUtil', '$stateParams', '$q', MyInfoController]);
app.controller('TeacherListController', ['$scope', 'UserService', 'ClassRoomService', '$stateParams', TeacherListController]);
app.controller('UserInfoController', ['$scope', 'UserService', '$stateParams', 'AppUtil', 'cordovaUtil', UserInfoController]);
app.controller('StudentListController', ['$scope', 'ClassRoomService', 'CourseService', '$stateParams', StudentListController]);

function TeacherListController($scope, UserService, ClassRoomService, $stateParams)
{
	$scope.title = "课程教师";
	$scope.emptyStr = "该课程暂无教师";
	var self = this;
	this.initService = function() {
		if ("course" == $stateParams.targetType) {
			self.targetService = self.loadCourseTeachers;
		} else if ("classroom" == $stateParams.targetType) {
			$scope.title = "班级教师";
			$scope.emptyStr = "该班级暂无教师";
			self.targetService = self.loadClassRoomTeachers;
		}
	};

	this.loadClassRoomTeachers = function() {
		ClassRoomService.getTeachers({
			classRoomId : $stateParams.targetId
		}, function(data) {
			$scope.users = data;
		});
	};

	this.loadCourseTeachers = function() {
		UserService.getCourseTeachers({
			courseId : $stateParams.targetId
		}, function(data) {
			$scope.users = data;
		});
	};

	$scope.loadUsers = function() {
		self.targetService();
	}

	$scope.getUserAvatar = function(user) {
		if (user.avatar) {
			return user.avatar;
		}

		if (user.mediumAvatar) {
			return user.mediumAvatar;
		}

		return "";
	}

	this.initService();
}

function StudentListController($scope, ClassRoomService, CourseService, $stateParams)
{
	$scope.title = getTitle($stateParams.targetType);

	function getTitle(targetType) {
		if ("classroom" == $stateParams.targetType) {
			return "班级学员";
		}

		return "课程学员";
	}

	function getEmptyStr(targetType) {
		if ("classroom" == $stateParams.targetType) {
			return "该班级暂无学员";
		}

		return "该课程暂无学员";
	}

	$scope.title = getTitle($stateParams.targetType);
	$scope.emptyStr = getEmptyStr($stateParams.targetType);

	function getClassRoomStudents(targetId, callback) {
		ClassRoomService.getStudents({
			classRoomId : $stateParams.targetId
		}, callback);
	}

	function getCourseStudents(targetId, callback) {
		CourseService.getStudents({
			courseId : $stateParams.targetId,
		}, callback);
	}

	function getStudentArray(resources) {
		var users = [];
		for (var i = 0; i < resources.length; i++) {
			users[i] = resources[i].user;
		};

		return users;
	}

	$scope.loadUsers = function() {
		var service;
		if ("classroom" == $stateParams.targetType) {
			service = getClassRoomStudents;
		} else {
			service = getCourseStudents;
		}
		service($stateParams.targetId, function(data) {
			$scope.users = getStudentArray(data.resources);
		});
	}

	$scope.getUserAvatar = function(user) {
		if (user.avatar) {
			return user.avatar;
		}

		if (user.mediumAvatar) {
			return user.mediumAvatar;
		}

		return "";
	}
}

function MyInfoController($scope, UserService, cordovaUtil, platformUtil, $stateParams, $q) 
{	
	var self = this;
	this.uploadAvatar = function(file) {
		$scope.showLoad();
		UserService.uploadAvatar({
			file : file.files[0]
		}, function(data) {
			$scope.hideLoad();
			if (data.error) {
				$scope.toast(data.error.message);
				return;
			}
			$scope.userinfo.fileId = data.id;
			$scope.userinfo.mediumAvatar = data.url;
		});
	};

	$scope.showSelectImage = function(e) {
		if (platformUtil.native && platformUtil.android) {
			e.preventDefault();
			cordovaUtil.uploadImage(
				$q,
				app.host + '/mapi_v2/User/uploadAvatar',
				{ token : $scope.token },
				{ file : "" },
				"image/*"
			).then(function(data) {
				if (! data) {
					alert("该功能仅支持客户端!");
					return;
				}
				$scope.userinfo.fileId = data.id;
				$scope.userinfo.mediumAvatar = data.url;
			});
		}
	};

	$scope.loadUserInfo = function() {
		$scope.showLoad();
		UserService.getUserInfo({
			userId : $scope.user.id
		}, function(data) {
			$scope.userinfo = data;
			$scope.hideLoad();
		});
	};

	$scope.generArray = ['female', 'male'];

	$scope.updateUserProfile = function() {
		var userinfo = $scope.userinfo;
		var params = {
			'fileId' : userinfo.fileId,
			'profile[nickname]' : userinfo.nickname,
			'profile[gender]' : userinfo.gender,
			'profile[signature]' : userinfo.signature
		};
		$scope.showLoad();
		UserService.updateUserProfile(params, function(data) {
			if (data.error) {
				$scope.toast(data.error.message);
				return;
			}
			$scope.toast("更新成功!");
			cordovaUtil.updateUser(data);
			$scope.hideLoad();
		});
	};

	$scope.uploadChange = function(file) {
		if (file && file.value) {
			self.uploadAvatar(file);
		}
	}
}

function UserInfoController($scope, UserService, $stateParams, AppUtil, cordovaUtil) 
{
	var self = this;

	$scope.isFollower = null;
	$scope.uiBarTransparent = true;

	$scope.changeTabStatus = function(headTop, scrollTop) {
		var transparent = scrollTop < headTop;
		if (transparent == $scope.uiBarTransparent) {
			return;
		}

		$scope.$apply(function() {
			$scope.uiBarTransparent = transparent;
		});
	}

	this.isTeacher = function(role) {
		return AppUtil.inArray('ROLE_TEACHER',role) > 0;
	}

	this.getUserLearnCourse = function() {
		UserService.getLearningCourseWithoutToken({
			userId : $stateParams.userId
		}, function(data) {
			$scope.courses = data.data;
		});
	}

	this.getUserTeachCourse = function() {
		UserService.getUserTeachCourse({
			userId : $stateParams.userId
		}, function(data) {
			$scope.courses = data.data;
		});
	}

	$scope.isUnOwner = function() {

		if ($scope.user && $scope.user.id == $stateParams.userId) {
			return false;
		}

		return true;
	};

	$scope.loadUserInfo = function() {
		$scope.showLoad();
		UserService.getUserInfo({
			userId : $stateParams.userId
		}, function(data) {
			$scope.hideLoad();
			if (! data) {
				$scope.toast("获取用户信息失败！");
				return;
			}
			$scope.userinfo = data;
			$scope.isTeacher = self.isTeacher(data.roles);
			if ($scope.isTeacher) {
				self.getUserTeachCourse();
			} else {
				self.getUserLearnCourse();
			}

			if ($scope.user) {
				UserService.searchUserIsFollowed({
					userId : $scope.user.id,
					toId : $stateParams.userId
				}, function(data) {
					$scope.isFollower = (true == data || "true" == data) ? true : false;
					console.log($scope.isFollower);
				});
			}
		});
	};

	this.follow = function() {
		UserService.follow({
			toId : $stateParams.userId
		}, function(data) {
			if (data && data.toId == $stateParams.userId) {
				$scope.isFollower = true;
				cordovaUtil.sendNativeMessage("refresh_friend_list", {});
			}
		});
	}

	this.unfollow = function() {
		UserService.unfollow({
			toId : $stateParams.userId
		}, function(data) {
			if (data) {
				$scope.isFollower = false;
				cordovaUtil.sendNativeMessage("refresh_friend_list", {});
			}
		});
	}

	$scope.changeFollowUser = function() {
		if (true == $scope.isFollower) {
			self.unfollow();
		} else {
			self.follow();
		}
		
	}
}

app.controller('TeacherTodoListController', ['$scope', '$stateParams', 'AnalysisService', TeacherTodoListController]);
function TeacherTodoListController($scope, $stateParams, AnalysisService) {

	Chart.defaults.global.tooltipTemplate = "<%= value %>";
	Chart.defaults.global.tooltipEvents = [""];
	Chart.defaults.global.animation = false;
	Chart.defaults.global.tooltipFillColor = "rgba(0,0,0,0)";
	Chart.defaults.global.tooltipFontColor = "#000";
	Chart.defaults.global.scaleLineColor = "rgba(0,0,0,0)";

	var self = this;

	$scope.initChartData = function() {
		$scope.showLoad();
		AnalysisService.getCourseChartData({
			courseId : $stateParams.courseId
		}, function(data) {
			$scope.hideLoad();
			if (data.error) {
				$scope.toast(data.error.message);
				return;
			}
			$scope.charts = data;
		});
	}

	$scope.loadCharts = function() {
		setTimeout(function(){
			for (var i = 0; i < $scope.charts.length; i++) {
				initChart($scope.charts[i], i);
			};
		}, 10);	
	};
	
	function initChart(chartData, id) {
		var ctx = document.getElementById("chart_" + id).getContext("2d");
		var chartLineColor = chartData.chartLineColor || "#37b97d";
		var data = {
		    labels: chartData.labelData,
		    datasets: [
		        {
		            label: "My First dataset",
		            fillColor: "rgba(0, 0, 0, 0)",
		            strokeColor: chartLineColor,
		            pointColor: chartLineColor,
		            pointStrokeColor: "#fff",
		            pointHighlightFill: chartLineColor,
		            pointHighlightStroke: chartLineColor,
		            data: chartData.pointData
		        }
		    ]
		};

		var defaults = {
			scaleShowGridLines : true,
			bezierCurve  : false,
			pointDot : true,
			pointDotRadius : 2
		};

		function showToolTips(lineChart) {
			var activePoints = lineChart.datasets[0].points;
			lineChart.eachPoints(function(point){
				point.restore(['fillColor', 'strokeColor']);
			});
			Chart.helpers.each(activePoints, function(activePoint){
				activePoint.fillColor = activePoint.highlightFill;
				activePoint.strokeColor = activePoint.highlightStroke;
			});
			lineChart.showTooltip(activePoints);
		}
		var myLineChart, chart = new Chart(ctx);
		var render = Chart.types.Line.prototype.render;

		Chart.types.Line.prototype.render = function(reflow) {
			var self = this;
			render.call(this, reflow);
			setTimeout(function() {
				showToolTips(self);
			}, 10);
		};

		myLineChart = chart.Line(data, defaults);
	}
}

app.controller('HomeworkTeachingController', ['$scope', '$stateParams', 'HomeworkManagerService', HomeworkTeachingController]);
function HomeworkTeachingController($scope, $stateParams, HomeworkManagerService) {

	var self = this;

	this.filter = function(data) {
		var users = data.users;
		var homeworkResults = data.homeworkResults;
		for (var i = 0; i < homeworkResults.length; i++) {
			homeworkResults[i]["user"] = users[homeworkResults[i]["userId"]];
		};
		data.homeworkResults = homeworkResults;
		console.log(data);
		return data;
	};

	$scope.showHomeWorkResult = function(homeworkResult) {
		alert("暂不支持在客户端批改作业");
	};

	$scope.initTeachingResult = function() {
		HomeworkManagerService.teachingResult({
			start : 3,
			courseId : $stateParams.courseId
		}, function(data) {
			$scope.teachingResult = self.filter(data);
		});
	};
}

app.controller('ThreadTeachingController', ['$scope', '$stateParams', 'ThreadManagerService', 'cordovaUtil', ThreadTeachingController]);
function ThreadTeachingController($scope, $stateParams, ThreadManagerService, cordovaUtil) {

	var self = this;

	$scope.courseId  =$stateParams.courseId;

	this.filter = function(data) {
		var users = data.users;
		var threads = data.threads;
		for (var i = 0; i < threads.length; i++) {
			threads[i]["user"] = users[threads[i]["userId"]];
		};
		data.threads = threads;
		return data;
	};

	$scope.showThreadChatView = function(thread) {
		cordovaUtil.startAppView("threadDiscuss", {
			type : "thread.post",
			courseId : thread.courseId,
			lessonId : thread.lessonId,
			threadId : thread.id
		});
	};

	$scope.initQuestionResult = function(limit) {
		ThreadManagerService.questionResult({
			start : limit,
			courseId : $stateParams.courseId
		}, function(data) {
			$scope.teachingResult = self.filter(data);
		});
	};
}