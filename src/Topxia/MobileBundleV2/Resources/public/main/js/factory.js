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
			var nativeFunc = window.esNativeCore ? esNativeCore[func]: null;
			
			self[func] = isNative ? nativeFunc : cordovaUtil[func];
		}

		return self;
	}
	
	return proxy();
}]);