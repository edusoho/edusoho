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
app.controller('CourseController', ['$scope', '$stateParams', 'ServcieUtil', 'AppUtil', '$state', CourseController]);
app.controller('CourseDetailController', ['$scope', '$stateParams', 'CourseService', CourseDetailController]);
app.controller('CourseSettingController', ['$scope', '$stateParams', 'CourseService', '$window', CourseSettingController]);

function CourseReviewController($scope, $stateParams, CourseService, $window)
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

  this.loadReviews = function() {
    CourseService.getReviews({
      start : $scope.start,
      limit : 10,
      courseId : $stateParams.courseId
    }, function(data) {

      var length  = data ? data.data.length : 0;
      if (!data || length == 0 || length < 10) {
          $scope.canLoad = false;
      }

      $scope.reviews = $scope.reviews || [];
      for (var i = 0; i < length; i++) {
        $scope.reviews.push(data.data[i]);
      };

      $scope.start += data.limit;

    });
  }

  this.loadReviewInfo = function() {
    CourseService.getCourseReviewInfo({
      courseId : $stateParams.courseId
    }, function(data) {
      $scope.reviewData = data;
    });
  }
  
  this.loadReviewInfo();
  this.loadReviews();
}

function CourseSettingController($scope, $stateParams, CourseService, $window)
{
  $scope.isLearn = $stateParams.isLearn;
  $scope.exitLearnCourse = function() {
    $scope.showLoad();
    CourseService.unLearnCourse({
      courseId : $stateParams.courseId,
      token : $scope.token
    }, function(data) {
      $scope.hideLoad();
      if (data.meta.code == 200) {
        $window.history.back();
        setTimeout(function() {
          $scope.$emit("refresh", {});
        }, 10);
        
      } else {
        $scope.toast(data.meta.message);
      }
    });
  }
}

function CourseDetailController($scope, $stateParams, CourseService)
{
  CourseService.getCourse({
      courseId : $stateParams.courseId,
      token : $scope.token
    }, function(data) {
      $scope.course = data.course;
    });
}

function CourseController($scope, $stateParams, ServcieUtil, AppUtil, $state)
{
    $scope.showLoad();

    var CourseService = ServcieUtil.getService("CourseService");
    var LessonService = ServcieUtil.getService("LessonService");

    CourseService.getCourse({
      courseId : $stateParams.courseId,
      token : $scope.token
    }, function(data) {
      $scope.ratingArray = AppUtil.createArray(5);
      $scope.vipLevels = data.vipLevels;
      $scope.course = data.course;
      $scope.member = data.member;
      $scope.isFavorited = data.userFavorited;
      $scope.discount = data.discount;

      if (data.member) {
        var progress = data.course.lessonNum == 0 ? 0 : data.member.learnedNum / data.course.lessonNum;
        $scope.learnProgress = (progress * 100) + "%" ;
      }
      $scope.courseView = app.viewFloder + (data.member ? "view/course_learn.html" : "view/course_no_learn.html");
      $scope.hideLoad();
      $scope.loadLessons();
      $scope.loadReviews();
    });

    $scope.loadLessons = function() {
      LessonService.getCourseLessons({
        courseId : $stateParams.courseId,
        token : $scope.token
      }, function(data) {
        $scope.$apply(function() {
          $scope.lessons = data.lessons;
          $scope.learnStatuses = data.learnStatuses;

          for( index in data.learnStatuses ) {
            $scope.lastLearnStatusIndex = index;
          }
          
        });
      });
    }

    $scope.loadReviews = function(){
      CourseService.getReviews({
        courseId : $stateParams.courseId,
        token : $scope.token,
        limit : 1
      }, function(data) {
        $scope.reviews = data.data;
      });
    }

    $scope.favoriteCourse = function() {
      if ($scope.user == null) {
        $state.go("login", { goto : "/course/" + $stateParams.courseId });
        return;
      }
      var params = {
          courseId : $stateParams.courseId,
          token : $scope.token
      };

      if ($scope.isFavorited) {
        CourseService.unFavoriteCourse(params, function(data) {
          if (data == true) {
            $scope.$apply(function() {
              $scope.isFavorited = false;
            });
          }
        });
      } else {
        CourseService.favoriteCourse(params, function(data) {
          if (data == true) {
            $scope.$apply(function() {
              $scope.isFavorited = true;
            });
          }
        });
      }
    }

    var self = this;
    this.payCourse = function() {
      ServcieUtil.getService("OrderService").payCourse({
        courseId : $stateParams.courseId,
        token : $scope.token
      }, function(data) {
        if (data.paid == true) {
          window.location.reload();
        } else {
          $scope.toast("加入学习失败!");
        }
      }, function(error) {
        console.log(error);
      });
    }

    $scope.vipLeand = function() {
      if ($scope.user == null) {
        $state.go("login", { goto : "/course/" + $stateParams.courseId });
        return;
      }
      CourseService.vipLearn({
        courseId : $stateParams.courseId,
        token : $scope.token
      }, function(data){
        if (data.meta.code == 200) {
          window.location.reload();
        } else {
          $scope.toast(data.meta.message);
        }
      }, function(error) {
        console.log(error);
      });
    }

    $scope.joinCourse = function() {
      if ($scope.user == null) {
        $state.go("login", { goto : "/course/" + $stateParams.courseId });
        return;
      }
      if ($scope.course.price <= 0) {
        self.payCourse();
      } else {
        $state.go("coursePay", { courseId : $scope.course.id });
      }
      
    }

    $scope.shardCourse = function() {
      if (! $scope.platform.native) {
        alert("请在客户端分享课程");
        return;
      }

      esNativeCore.share("", "课程", "关于", $scope.course.largePicture);
    }

    $scope.$parent.$on("refresh", function(event, data) {
      window.location.reload();
    });

    $scope.learnLesson = function(lesson) {
      if (! $scope.member && 1 != lesson.free) {
        alert("请先加入学习");
        return;
      }

      if ("text" == lesson.type) {
        $state.go("lesson",  { courseId : lesson.courseId, lessonId : lesson.id } );
        return;
      }

      if (! $scope.platform.native) {
        alert("请在客户端学习非图文课时");
        return;
      }

      esNativeCore.learnCourseLesson(lesson.courseId, lesson.id);
    }
};
app.controller('CourseListController', ['$scope', '$stateParams', '$state', 'CourseUtil', 'CourseService', 'CategoryService', CourseListController]);
function CourseListController($scope, $stateParams, $state, CourseUtil, CourseService, CategoryService)
{
    $scope.categoryTab = {
      category : "分类",
      type : "全部分类",
      sort : "综合排序",
    };

    $scope.canLoad = true;
    $scope.start = $scope.start || 0;

    console.log("CourseListController");
      $scope.loadMore = function(){
            if (! $scope.canLoad) {
              return;
            }
           setTimeout(function() {
              $scope.loadCourseList($stateParams.sort);
           }, 200);
         
      };

      $scope.loadCourseList = function(sort) {
             $scope.showLoad();
              CourseService.searchCourse({
                limit : 10,
                start: $scope.start,
                categoryId : $stateParams.categoryId,
                sort : sort,
                type : $stateParams.type
              }, function(data) {
                        $scope.hideLoad();
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

      $scope.courseListSorts = CourseUtil.getCourseListSorts();
      $scope.courseListTypes = CourseUtil.getCourseListTypes();

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
             $stateParams.type = null;
             $stateParams.categoryId  =category.id;
             $scope.loadCourseList($scope.sort);
      }

      $scope.loadCourseList();
};
app.controller('CourseNoticeController', ['$scope', 'CourseService', '$stateParams', CourseNoticeController]);

function CourseNoticeController($scope, CourseService, $stateParams)
{
	var limit = 10;
	$scope.start = 0;
	$scope.showLoadMore = true;
	
	function loadNotices(start, limit) {
		CourseService.getCourseNotices({
			start : $scope.start,
			limit : limit,
			courseId : $stateParams.courseId
		}, function(data) {
			$scope.notices = $scope.notices || [];
			
			if (! data || data.length == 0) {
				$scope.showLoadMore = false;
				$scope.toast("没有更多消息");
				return;
			}
			
			for (var i = 0; i < data.length; i++) {
		                  $scope.notices.push(data[i]);
		           };
			$scope.start += limit;
		});
	}

	$scope.loadMore = function() {
		loadNotices($scope.start, limit);
	}

	loadNotices($scope.start, limit);
};
app.controller('FoundCourseController', ['$scope', 'SchoolService', '$state', FoundCourseController]);

function FoundCourseController($scope, SchoolService, $state)
{
	console.log("FoundCourseController");
	SchoolService.getSchoolBanner(function(data) {
		$scope.banners = data;
	});
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


app.controller('FoundClassRoomController', ['$scope', '$http', FoundClassRoomController]);

function FoundClassRoomController($scope, ClassRoomService, SchoolService)
{
	console.log("FoundClassRoomController");

	SchoolService.getSchoolBanner(function(data) {
		$scope.banners = data;
	});

  	ClassRoomService.getClassRooms({ limit : 3 }, function(data) {
  		$scope.classRooms = data.data;
  	});
};
app.controller('LessonController', ['$scope', '$stateParams', 'LessonService', LessonController]);

function LessonController($scope, $stateParams, LessonService)
{	
	var self = this;

	self.loadLesson = function() {
		LessonService.getLesson({
			courseId : $stateParams.courseId,
			lessonId : $stateParams.lessonId
		},function(data) {
			$scope.lesson = data;
			$scope.lessonView = "view/lesson_" + data.type + ".html";
		});
	}

	this.loadLesson();
};
app.controller('LoginController', ['$scope', 'UserService', '$state', '$stateParams', '$window', LoginController]);

function LoginController($scope, UserService, $state, $stateParams, $window)
{	
	console.log("LoginController");

	$scope.user = {
		username : null,
		password : null
	};

	$scope.jumpToMain = function() {
		$state.go("slideView.mainTab");
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

			if ($stateParams.goto) {
				$window.history.back();
				setTimeout(function() {
				         $scope.$emit("refresh", {});
				}, 10);
			} else {
				$scope.jumpToMain();
			}
			
    		});
    	}
};
function MainTabController($scope, sideDelegate, $state)
{
	console.log("MainTabController");
}

app.controller('FoundTabController', ['$scope', 'CategoryService', 'AppUtil', 'sideDelegate', '$state', FoundTabController]);

function FoundTabController($scope, CategoryService, AppUtil, sideDelegate, $state)
{
	console.log("FoundTabController");
	$scope.toggleView = function(view) {
		$state.go("slideView.mainTab." + view);
	};

	$scope.toggle = function() {
		
		if ($scope.platform.native) {
			window.esNativeCore.openDrawer("open");
			return;
		}

		sideDelegate.toggleMenu();
	};

	$scope.categorySelectedListener  = function(category) {
		$state.go('courseList' , { categoryId : category.id } );
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
			if ($scope.platform.native) {
				esNativeCore.openWebView(app.rootPath + "#/course/" + params);
				return;
			}
			$state.go("course", { courseId : params });
		}

		this.webviewAction = function(params) {
			if ($scope.platform.native) {
				esNativeCore.openWebView(params);
				return;
			}
			window.location.href = params;
		}

		this.noneAction = function() {
		}

		return this[action + "Action"];
	}

	$scope.bannerClick = function(banner) {
		var bannerAction = self.parseBannerAction(banner.action);
		bannerAction(banner.params);
	}
}


app.controller('MessageTabController', ['$scope', FoundTabController]);

function MessageTabController($scope)
{
	console.log("MessageTabController");
}

app.controller('ContactTabController', ['$scope', ContactTabController]);

function ContactTabController($scope)
{
	console.log("ContactTabController");
	console.log($scope);
}
;
app.controller('MyFavoriteController', ['$scope', 'httpService', '$timeout', MyFavoriteController]);

function MyFavoriteController($scope, CourseService, CourseUtil, $timeout)
{
	console.log("MyFavoriteController");
	var self = this;
	$scope.data  = CourseUtil.getFavoriteListTypes();

  	this.loadDataList = function(type) {
  		var dataList = $scope.data[type];
  		CourseService.getFavoriteCourse(
  			dataList.url,
  			{
	  			limit : 100,
				start: dataList.start,
				token : $scope.token
			}, function(data) {
	  			if (!data || data.data.length == 0) {
		    			dataList.canLoad = false;
		    		}

		    		dataList.data = dataList.data.concat(data.data);
		    		dataList.start += data.limit;

		    		if (data.total && dataList.start >= data.total) {
		    			dataList.canLoad = false;
		    		}
  			}
  		);
  	}

  	this.loadCourses = function() {
  		self.loadDataList("course");
  	}

  	this.loadLiveCourses = function() {
  		self.loadDataList("live");
  	}

  	this.loadCourses();
  	this.loadLiveCourses();
};
app.controller('MyGroupQuestionController', ['$scope', 'QuestionService', MyGroupQuestionController]);

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
        type : type,
        token : $scope.token
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

function MyGroupNoteController($scope, NoteService)
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

app.controller('MyGroupNoteController', ['$scope', 'NoteService', MyGroupNoteController]);

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

app.controller('MyGroupThreadController', ['$scope', 'QuestionService', MyGroupThreadController]);
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
};
app.controller('MyLearnController', ['$scope', 'CourseService', MyLearnController]);

function MyLearnController($scope, CourseService)
{
	var self = this;
	self.content = {
		course : {
			start : 0,
			canLoad : true,
			data : []
		},
		live : {
			start : 0,
			canLoad : true,
			data : []
		}
	};

	$scope.course = self.content.course;
	$scope.live = self.content.live;

  	self.loadDataList = function(content, serviceCallback) {
  		serviceCallback({
  			limit : 10,
			start: content.start,
			token : $scope.token
  		}, function(data) {

  			if (!data || data.data.length == 0) {
	    			content.canLoad = false;
	    		}

	    		content.data = content.data.concat(data.data);
	    		content.start += data.limit;

	    		if (data.total && content.start >= data.total) {
	    			content.canLoad = false;
	    		}
  		});
  	}

  	$scope.canLoadMore = function(type) {
  		return self.content[type].canLoad;
  	};

  	$scope.loadMore = function(type){
  		$scope.loadDataList(type);
  	};

  	$scope.loadCourses = function() {
  		self.loadDataList(self.content.course, CourseService.getLearningCourse);
  	}

  	$scope.loadLiveCourses = function() {
  		self.loadDataList(self.content.live, CourseService.getLiveCourses);
  	}

  	$scope.loadCourses();
  	$scope.loadLiveCourses();
};
app.controller('CoursePayController', ['$scope', '$stateParams', 'ServcieUtil', 'AppUtil', CoursePayController]);
app.controller('CourseCouponController', ['$scope', 'CouponService', '$stateParams', '$window', CourseCouponController]);
app.controller('VipListController', ['$scope', '$stateParams', 'SchoolService', VipListController]);
app.controller('VipPayController', ['$scope', '$stateParams', 'SchoolService', 'VipUtil', VipPayController]);


function VipPayController($scope, $stateParams, SchoolService, VipUtil)
{
	$scope.showLoad();
	SchoolService.getVipPayInfo({
		levelId : $stateParams.levelId,
		token : $scope.token
	}, function(data) {
		$scope.hideLoad();
		$scope.data = data;
		$scope.payModes = VipUtil.getPayMode(data.buyType);
		$scope.selectedNum = 1;
		$scope.selectedPayMode = $scope.payModes[0];

		$scope.sumTotalPirce();
		$scope.initPopver();
	});
	
	$scope.sumTotalPirce = function() {
		var level = $scope.data.level;
		var payTypes = VipUtil.getPayType();

		var price = $scope.selectedPayMode.type == payTypes.byMonth ? level.monthPrice : level.yearPrice;
		$scope.totalPayPrice = $scope.selectedNum * price;
	}

	$scope.add = function() {
		if ($scope.selectedNum < 12) {
			$scope.selectedNum ++;
			$scope.sumTotalPirce();
		}
	}

	$scope.sub = function() {
		if ($scope.selectedNum > 1) {
			$scope.selectedNum --;
			$scope.sumTotalPirce();
		}
	}

	$scope.initPopver = function() {

		  $scope.showPopover = function($event) {
		  	$scope.isShowPayMode = ! $scope.isShowPayMode ;
		  };

		  $scope.selectPayMode = function(payMode) {
		  	$scope.selectedPayMode = payMode;
			$scope.sumTotalPirce();
		  	$scope.isShowPayMode = false;
		  }
	}	

}

function VipListController($scope, $stateParams, SchoolService)
{
	SchoolService.getSchoolVipList({
		userId : $scope.user.id
	}, function(data) {
		$scope.data = data;
	});
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

function CoursePayController($scope, $stateParams, ServcieUtil, AppUtil)
{
	var self = this;
	ServcieUtil.getService("OrderService").getPayOrder({
		courseId : $stateParams.courseId,
		token : $scope.token
	}, function(data) {
		$scope.$apply(function() {
			$scope.data = data;
		});
	});

	$scope.$parent.$on("coupon", function(event, data) {
		$scope.$apply(function() {
			$scope.coupon = data.coupon;
		});
	});

	$scope.selectCoupon = function() {
		self.dialog = $(".ui-dialog");
		self.dialog.dialog("show");
	}

	$scope.pay = function() {
		var CourseService = ServcieUtil.getService("CourseService");
		ServcieUtil.getService("OrderService").payCourse({
			courseId : $stateParams.courseId,
        			token : $scope.token
		}, function(data) {
			if (data.status == "ok" && data.payUrl != "") {
				if (! $scope.platform.native) {
					alert("请在客户端内支付!");
					return;
				}
				esNativeCore.payCourse("支付课程", data.payUrl);
			}
		});
	}

	$scope.formData = { code : "" };
	$scope.checkCoupon = function() {
		$scope.formData.error = "";
		$scope.showLoad();
		ServcieUtil.getService("CouponService").checkCoupon({
			courseId : $stateParams.courseId,
			type : "course",
			code : $scope.formData.code
		}, function(data) {
			$scope.hideLoad();
			if (data.meta.code != 200) {
				$scope.formData.error = data.meta.message;
				return;
			}
			$scope.$emit("coupon", { coupon : data.data });
			$scope.close();

		}, function(data) {
			$scope.hideLoad();
			$scope.toast("检验优惠码错误");
		});
	}

	$scope.close = function() {
		self.dialog.dialog("hide");
	}
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
			threadId : $stateParams.threadId,
			token : $scope.token
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
			threadId : $stateParams.threadId,
			token : $scope.token
		}, function(data) {
			$scope.teacherPosts = data;
		});
	}

	this.loadTheadPost = function() {
		QuestionService.getThreadPost({
			courseId: $stateParams.courseId,
			threadId : $stateParams.threadId,
			token : $scope.token
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
			noteId: $stateParams.noteId,
			token : $scope.token
		}, function(data) {
			$scope.note = data;
			$scope.hideLoad();
		});
	}

	self.loadNote();
};
app.controller('RegistController', ['$scope', '$http', 'UserService', '$state', RegistController]);

function RegistController($scope, $http, UserService, $state)
{
	console.log("RegistController");

	$scope.user = {
		phone : null,
		code : null,
		password: null
	};

	$scope.jumpToMain = function() {
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
			$scope.toast(data.meta.message);
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

		$scope.showLoad();
		UserService.regist({
			phone : user.phone,
			smsCode : user.code,
			password : user.password,
		}, function(data) {
			console.log(data);
			if (data.meta.code == 500) {
				$scope.toast(data.meta.message);
				return;
			}
			$scope.hideLoad();
			$scope.jumpToMain();
		}, function(error) {
			$scope.toast("注册失败");
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

		$scope.showLoad();
		UserService.regist({
			email : user.email,
			password : user.password,
		}, function(data) {
			if (data.meta.code == 500) {
				$scope.toast(data.meta.message);
				return;
			}
			$scope.hideLoad();
			$scope.jumpToMain();
		}, function(error) {
			$scope.toast("注册失败");
		});
	}
};
app.controller('SettingController', ['$scope', 'UserService', '$state', SettingController]);

function SettingController($scope, UserService, $state)
{
	$scope.isShowLogoutBtn = $scope.user ? true : false;
	$scope.logout = function() {
		$scope.showLoad();
		UserService.logout({
			token : $scope
		}, function(data) {
			$scope.hideLoad();
			$state.go("slideView.mainTab.found");
		});
	}
};
app.controller('MyInfoController', ['$scope', 'httpService', '$stateParams', MyInfoController]);
app.controller('TeacherListController', ['$scope', 'UserService', '$stateParams', TeacherListController]);
app.controller('UserInfoController', ['$scope', 'UserService', '$stateParams', 'AppUtil', UserInfoController]);

function TeacherListController($scope, UserService, $stateParams)
{
	UserService.getCourseTeachers({
		courseId : $stateParams.courseId
	}, function(data) {
		$scope.users = data;
	});
}

function MyInfoController($scope, httpService, $stateParams) 
{
	httpService.get({
		url : app.host + "/mapi_v2/User/getUserInfo",
		params : {
			userId : $scope.user.id
		},
		success : function(data) {
			$scope.userinfo = data;
		},
		error : function(data) {
			$ionicLoading.hide();
		}
	});
}

function UserInfoController($scope, UserService, $stateParams, AppUtil) 
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

	UserService.getUserInfo({
		userId : $stateParams.userId
	}, function(data) {
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
				$scope.isFollower = true == data ? true : false;
			});
		}
	});

	this.follow = function() {
		UserService.follow({
			token : $scope.token,
			toId : $stateParams.userId
		}, function(data) {
			if (data && data.toId == $stateParams.userId) {
				$scope.isFollower = true;
			}
		});
	}

	this.unfollow = function() {
		UserService.unfollow({
			token : $scope.token,
			toId : $stateParams.userId
		}, function(data) {
			if (data) {
				$scope.isFollower = false;
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