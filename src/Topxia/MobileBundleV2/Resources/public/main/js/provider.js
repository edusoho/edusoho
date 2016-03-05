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
});