var app = angular.module('EduSohoApp', [
            'ngSanitize',
            'ui.router',
             'AppService',
            'AppFactory',
            'AppProvider',
            'ngSideView',
            'pasvaz.bindonce'
  ]);

app.viewFloder = "/bundles/topxiamobilebundlev2/esmobile/";

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

app.config([ '$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider)
{
  $urlRouterProvider.when("/", "/index").
  otherwise('/');

  $stateProvider.
            state("slideView",{
                abstract: true,
                views : {
                    "rootView" : {
                        templateUrl : app.viewFloder  + 'view/main.html',
                        controller : AppInitController
                    }
                }
            }).
          state("slideView.mainTab",{
            url : "/index",
            views : {
              "menuContent" : {
                templateUrl : app.viewFloder  + 'view/main_content.html',
                controller : FoundTabController
              }
            }
          }).state('slideView.mainTab.found', {
              url: "/found",
              views: {
                'found-tab': {
                  templateUrl: app.viewFloder  + "view/found.html",
                  controller: FoundTabController
                }
              }
            }).state('slideView.mainTab.found.course', {
              url: "/course",
              views: {
                'found-course': {
                  templateUrl: app.viewFloder  + "view/found_course.html",
                  controller: FoundCourseController
                },
                 'found-live': {
                  templateUrl: app.viewFloder  + "view/found_live.html",
                  controller: FoundLiveController
                },
                'found-classroom': {
                  templateUrl: app.viewFloder  + "view/found_classroom.html",
                  controller: FoundClassRoomController
                }
              }
            });

            $stateProvider.state('courseList', {
              url: "/courselist/:categoryId",
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
              url: "/myinfo/:userId",
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
                  templateUrl: app.viewFloder  + "view/myfavorite.html",
                  controller : MyFavoriteController
                }
              }
            })

            $stateProvider.state('mygroup', {
              url: "/mygroup",
              views: {
                'rootView': {
                  templateUrl: app.viewFloder  + "view/mygroup.html"
                }
              }
            }).state('mygroup.question', {
              url: "/question",
              views: {
                'mygroup-question': {
                  templateUrl: app.viewFloder  + "view/mygroup_question.html",
                  controller : MyGroupQuestionController
                }
              }
            }).state('mygroup.note', {
              url: "/note",
              views: {
                'mygroup-note': {
                  templateUrl: app.viewFloder  + "view/mygroup_note.html",
                  controller : MyGroupNoteController
                }
              }
            }).state('mygroup.thread', {
              url: "/thread",
              views: {
                'mygroup-thread': {
                  templateUrl: app.viewFloder  + "view/mygroup_thread.html",
                  controller : MyGroupQuestionController
                }
              }
            });

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
              url: "/teacherlist/:courseId",
              views: {
                'rootView': {
                  templateUrl: app.viewFloder  + "view/teacher_list.html",
                  controller : TeacherListController
                }
              }
            });

            $stateProvider.state('coursePay', {
              url: "/coursepay/:courseId",
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
              url: "/coursenotice/:courseId",
              views: {
                'rootView': {
                  templateUrl: app.viewFloder  + "view/course_notice.html",
                  controller : CourseNoticeController
                }
              }
            });

            $stateProvider.state('courseReview', {
              url: "/coursereview/:courseId",
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
}]);

app.run(["applicationProvider", "$rootScope", '$timeout',
  function(applicationProvider, $rootScope, $timeout) {

  $rootScope.platform = browser.v;
  $rootScope.showLoad = function(template) {
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
  angular.$client = {};

  applicationProvider.init(app.host);
  FastClick.attach(document.body);
}]);

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

if (browser.v.native) {
  document.addEventListener("deviceready", function() {
      angular.bootstrap( document, ["EduSohoApp"] );
  });
} else {
  angular.element(document).ready(function() {
    angular.bootstrap( document, ["EduSohoApp"] );
  });
}
;
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
		httpService.simpleGet("/mapi_v2/Order/payCourse", arguments);
	}

	this.getPayOrder = function() {
		httpService.simpleGet('/mapi_v2/Order/getPayOrder', arguments);
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
		httpService.simpleGet('/mapi_v2/Course/coupon', arguments);
	}
}]).
service('UserService', ['httpService', 'applicationProvider', function(httpService, applicationProvider) {

	this.follow = function(params, callback) {
		httpService.simpleGet("/mapi_v2/User/follow", arguments);
	};

	this.unfollow = function(params, callback) {
		httpService.simpleGet("/mapi_v2/User/unfollow", arguments);
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
			params : params,
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
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
				if (data.data) {
					applicationProvider.setUser(data.data.user, data.data.token);
				}
			},
			error : function(data) {
			}
		});
	}

	this.login = function(params, callback) {
		httpService.post({
			url : app.host + '/mapi_v2/User/login',
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
				if (data) {
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
			params : params,
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
		httpService.simpleGet('/mapi_v2/Course/favoriteCourse', arguments);
	}

	this.unFavoriteCourse = function(params, callback) {
		httpService.simpleGet('/mapi_v2/Course/unFavoriteCourse', arguments);
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
			params : {
				limit : params.limit,
				start: params.start,
				categoryId : params.categoryId,
				sort : params.sort,
				type : params.type
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
service('httpService', ['$http', '$rootScope', function($http, $rootScope) {
	
	var self = this;
	this.getOptions = function(url, params, callback, errorCallback) {
		return {
			method : "get",
			url : app.host + url,
			params : params,
			success : function(data, status, headers, config) {
				callback(data);
			},
			error : function(data) {
				if (errorCallback) {
					errorCallback(data);
				} 
			}
		};
	}

	this.simpleGet = function(url) {
		params  = arguments[1][0];
		callback = arguments[1][1];
		errorCallback = arguments[1][2];

		var options = self.getOptions(url, params, callback, errorCallback);
		var http = $http(options).success(options.success);

		if (options.error) {
			http.error(options.error);
		} else {
			http.error(function(data) {
				console.log(data);
			});
		}

	}

	this.get = function(options) {
		options.method  = "get";
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
		var http = $http(options).success(options.success);

		if (options.error) {
			http.error(options.error);
		} else {
			http.error(function(data) {
				console.log(data);
			});
		}
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
}]).
filter('coverIncludePath', function() {
	return function(path) {
		return app.viewFloder + path;
	}
}).
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
			return src;
		}
		return app.viewFloder  + "img/avatar.png";
	}
}]);;
var appFactory = angular.module('AppFactory', []);
appFactory.factory('AppUtil', ['$rootScope', '$timeout', function($rootScope, $timeout) {
	var utils = {
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
factory('VipUtil', function() {

	var payByYear = {
		title : "按年支付",
		type : 20
	};

	var payByMonth  ={
		title : "按月支付",
		type : 30
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
					data : [],
					start : 0,
					canLoad : true
				},
				'live' : {
					url : "Course/getFavoriteLiveCourse",
					data : [],
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
		  			name : "班级",
		  			type : "classroom"
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
		}
	};
}]).
factory('ImageUtil', ['$rootScope', function($rootScope){
	function getScreenWidth() {
		var width = window.screen.width;
		switch (window.orientation) {
		case 0:
			width = window.screen.width;
			break;
		case 90:
		case - 90 : width = window.screen.height;
			break
		}
		width = width * 0.96;
		return width
	}
	function zoomImage(img, width) {
		var oldH = img.height;
		var oldW = img.width;
		img.width = width;
		img.height = width / oldW * oldH
	}
	function adaptationImage() {
		var width = getScreenWidth();
		var imgs = angular.element(document.images);
		for (var i = 0; i < imgs.length; i++) {
			zoomImage(imgs[i], width)
		}
	}

	var util = {
		zoom : function(){
			var imageArray = new Array();
			var imgs = angular.element(document.images);
			for (var i = 0; i < imgs.length; i++) {
				var img = imgs[i];
				img.addEventListener('load',
				function() {
					var width = getScreenWidth();
					zoomImage(this, width)
				});
				img.alt = i;
				imageArray.push(img.src);
				img.addEventListener('click',
				function() {
					window.location = 'imageIndexNUrls://?' + this.alt + '.partation.' + imageArray.join('.partation.');
					window.jsobj.showImages(this.alt,imageArray);
				})
			}
			window.addEventListener('orientationchange',
			function() {
				adaptationImage()
			},
			false);
		}
	};

	return util;
	
}]);;
var appProvider= angular.module('AppProvider', []);
appProvider.provider('applicationProvider', function() {

	var self = this;
	this.$get = function(localStore, $rootScope, $q) {
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
			if ($rootScope.platform.native) {
				var promise = esNativeCore.getUserToken($q);
				promise.then(function(data) {
					application.user = data.user;
					application.token = data.token;
      					application.updateScope($rootScope);
				});
				return;
			}
			application.user = angular.fromJson(localStore.get("user"));
			application.token = localStore.get("token");
			application.updateScope($rootScope);
		}

		application.clearUser = function() {
			this.user = null;
			this.token = null;
			$rootScope.user = null;
			$rootScope.token = null;
			localStore.remove("user");
			localStore.remove("token");
			if ($rootScope.platform.native) {
				esNativeCore.clearUserToken();
			}
		}

		application.setUser = function(user, token) {
			this.user = user;
			this.token = token;
			this.updateScope($rootScope);
			localStore.save("user", angular.toJson(user));
			localStore.save("token", token);
			if ($rootScope.platform.native) {
				console.log(token);
				esNativeCore.saveUserToken(user, token);
			}
		}

		application.updateScope = function($scope) {
			$scope.user = application.user;
			$scope.token = application.token;
		}
	    	return application;
	  }
});