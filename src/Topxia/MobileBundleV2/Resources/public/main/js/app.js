var app = angular.module('app', [
            'ngSanitize',
            'ui.router',
            'AppService',
            'AppFactory',
            'AppProvider',
            'ngSideView',
            'pasvaz.bindonce'
  ]);

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
                  templateUrl: app.viewFloder  + "view/myfavorite.html"
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
            $stateProvider.state('search', {
              url: "/search",
              views: {
                'rootView': {
                  templateUrl: app.viewFloder  + "view/search.html",
                  controller : SearchController
                }
              }
            });
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
  angular.$client = {};

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
