var app = angular.module('EduSohoApp', [
    	       'ionic',
    	       'AppService',
            'AppFactory',
            'AppProvider',
            'ngSideView',
            'pasvaz.bindonce'
	]);

app.viewFloder = "/bundles/topxiamobilebundlev2/esmobile/";

app.config(['$httpProvider', '$ionicConfigProvider', function($httpProvider, $ionicConfigProvider) {

    $ionicConfigProvider.views.maxCache(1); 
    $ionicConfigProvider.views.forwardCache(false);

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
	$urlRouterProvider.when("/", "/index/found").
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
				controller : MainTabController
			}
		}
	}).state('slideView.mainTab.message', {
              url: "/message",
              views: {
                'message-tab': {
                  templateUrl: app.viewFloder  + "view/message.html",
                  controller: MessageTabController
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
            }).state('slideView.mainTab.contact', {
              url: "/contact",
              views: {
                'contact-tab': {
                  templateUrl: app.viewFloder  + "view/contact.html",
                  controller: ContactTabController
                }
              }
            }).state('slideView.mainTab.found.course', {
              url: "/course",
              views: {
                'found-course': {
                  templateUrl: app.viewFloder  + "view/found_course.html",
                  controller: FoundCourseController
                }
              }
            }).state('slideView.mainTab.found.live', {
              url: "/live",
              views: {
                'found-live': {
                  templateUrl: app.viewFloder  + "view/found_live.html",
                  controller: FoundLiveController
                }
              }
            }).state('slideView.mainTab.found.classroom', {
              url: "/classroom",
              views: {
                'found-classroom': {
                  templateUrl: app.viewFloder  + "view/found_classroom.html",
                  controller: FoundClassRoomController
                }
              }
            });

            $stateProvider.state('courseList', {
              url: "/courselist",
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
              url: "/login",
              views: {
                'rootView': {
                  templateUrl: app.viewFloder  + "view/login.html",
                  controller: LoginController
                }
              }
            }).state('regist', {
              url: "/regist",
              abstract: true,
              views: {
                'rootView': {
                  templateUrl: app.viewFloder  + "view/regist.html",
                  controller: RegistController
                }
              }
            }).state('regist.phone', {
              url: "/phone",
              views: {
                'regist-phone': {
                  templateUrl: app.viewFloder  + "view/regist_phone.html"
                }
              }
            }).state('regist.email', {
              url: "/email",
              views: {
                'regist-email': {
                  templateUrl: app.viewFloder  + "view/regist_email.html"
                }
              }
            });

            $stateProvider.state('userinfo', {
              url: "/userinfo/:userId",
              views: {
                'rootView': {
                  templateUrl: app.viewFloder  + "view/userinfo.html",
                  controller: UserInfoController
                }
              }
            });

            $stateProvider.state('mylearn', {
              url: "/mylearn",
              views: {
                'rootView': {
                  templateUrl: app.viewFloder  + "view/mylearn.html",
                }
              }
            }).state('mylearn.course', {
              url: "/course",
              views: {
                'mylearn-course': {
                  templateUrl: app.viewFloder  + "view/mylearn_course.html",
                  controller : MyLearnCourseController
                }
              }
            }).state('mylearn.live', {
              url: "/live",
              views: {
                'mylearn-live': {
                  templateUrl: app.viewFloder  + "view/mylearn_live.html",
                  controller : MyLearnLiveController
                }
              }
            }).state('mylearn.classroom', {
              url: "/classroom",
              views: {
                'mylearn-classroom': {
                  templateUrl: app.viewFloder  + "view/mylearn_classroom.html",
                  controller : MyLearnClassRoomController
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
            }).state('myfavorite.course', {
              url: "/course",
              views: {
                'myfavorite-course': {
                  templateUrl: app.viewFloder  + "view/myfavorite_course.html",
                }
              }
            }).state('myfavorite.live', {
              url: "/live",
              views: {
                'myfavorite-live': {
                  templateUrl: app.viewFloder  + "view/myfavorite_live.html",
                }
              }
            });

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

            $urlRouterProvider.when("/regist", "/regist/phone");
}]);

app.run(["applicationProvider", "$rootScope", '$ionicConfig', '$ionicLoading', '$timeout',
  function(applicationProvider, $rootScope, $ionicConfig, $ionicLoading, $timeout) {
  
  var browser={
      v: (function(){
          var u = navigator.userAgent;
          return {
              native: u.indexOf('kuozhi') > -1, //是否native应用程序，没有头部与底部
          };
      })()
  };

  $ionicConfig.platform.native = browser.v.native;

  if ($ionicConfig.platform.android) {
    $ionicConfig.setPlatformConfig('android', {
      tabs: {
        style: 'android',
        position: 'top'
      }
    });
  }
  
  $rootScope.platform = $ionicConfig.platform;
  $rootScope.showLoad = function(template) {
    $ionicLoading.show({
            template: template || '加载中...',
    });
  };

  $rootScope.toast = function(template) {
    $ionicLoading.show({
            template: template || '加载中...',
    });

    $timeout(function() {
      $ionicLoading.hide();
    }, 2000);
  };

  $rootScope.hideLoad = function() {
    $ionicLoading.hide();
  };

  app.host = window.location.origin;
  applicationProvider.init(app.host);
  applicationProvider.updateScope($rootScope);

  $rootScope.stateParams = {};
  angular.$client = {};
}]);
