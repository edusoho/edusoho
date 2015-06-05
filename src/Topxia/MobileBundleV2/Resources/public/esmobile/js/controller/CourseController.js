app.controller('CourseListController', ['$scope', '$stateParams', 'AppUtil', 'CourseUtil', 'CourseService', 'CategoryService', CourseListController]);
app.controller('CourseController', ['$scope', '$stateParams', 'ServcieUtil', 'AppUtil', '$ionicScrollDelegate', '$state', '$ionicTabsDelegate', CourseController]);
app.controller('CourseDetailController', ['$scope', '$stateParams', 'CourseService', CourseDetailController]);
app.controller('CourseSettingController', ['$scope', '$stateParams', 'CourseService', '$ionicHistory', CourseSettingController]);

function CourseSettingController($scope, $stateParams, CourseService, $ionicHistory)
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
        $ionicHistory.goBack();
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

function CourseController($scope, $stateParams, ServcieUtil, AppUtil, $ionicScrollDelegate, $state, $ionicTabsDelegate)
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
        $scope.learnProgress = (data.member.learnedNum / data.course.lessonNum * 100) + "%" ;
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
        $scope.lessons = data.lessons;
        $scope.learnStatuses = data.learnStatuses;
      });
    }

    $scope.loadReviews = function(){
      CourseService.getReviews({
        courseId : $stateParams.courseId,
        token : $scope.token,
        limit : 1
      }, function(data) {
        console.log(data.data);
        $scope.reviews = data.data;
      });
    }

    $scope.favoriteCourse = function() {
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
      if ($scope.course.price <= 0) {
        self.payCourse();
      } else {
        $state.go("coursePay", { courseId : $scope.course.id });
      }
      
    }

    $scope.$parent.$on("refresh", function(event, data) {
      window.location.reload();
    });

    $scope.onTabSelected = function(tabScope) {
      $scope.selectedIndex = $ionicTabsDelegate.$getByHandle("tabHandle").selectedIndex();
    }
}

function CourseListController($scope, $stateParams, AppUtil, CourseUtil, CourseService, CategoryService)
{
	$scope.canLoad = true;
	$scope.start = $scope.start || 0;
      var callbackType = {
        infinite : 'scroll.infiniteScrollComplete',
        refresh  : 'scroll.refreshComplete'
      };

	console.log("CourseListController");
	$scope.canLoadMore = function() {
  		return $scope.canLoad;
  	};

  	$scope.loadMore = function(){
  		$scope.loadCourseList($stateParams.sort, callbackType.infinite);
  	};

  	$scope.loadCourseList = function(sort, callbackType) {
           $scope.showLoad();
  		CourseService.searchCourse({
  			limit : 10,
			start: $scope.start,
			categoryId : $stateParams.categoryId,
			sort : sort,
                 type : $stateParams.type
  		}, function(data) {
                $scope.hideLoad();
  			if (!data || data.data.length == 0 ) {
	    			$scope.canLoad = false;
	    		}

                $scope.courses = $scope.courses || [];
                for (var i = 0; i < data.data.length; i++) {
                  $scope.courses.push(data.data[i]);
                };

	    		//$scope.courses = $scope.courses ? $scope.courses.concat(data.data) : data.data;
	    		$scope.start += data.limit;

	    		$scope.$broadcast(callbackType);
  		});
  	}

  	$scope.courseListSorts = CourseUtil.getCourseListSorts();
  	$scope.courseListTypes = CourseUtil.getCourseListTypes();

  	CategoryService.getCategorieTree(function(data) {
		$scope.categoryTree = data;
	});

  	$scope.selectType = function(type) {
  		clearData();
           $stateParams.type  = type;
           setTimeout(function(){
              $scope.loadCourseList($scope.sort, callbackType.infinite);
           }, 100);
  	}

  	function clearData() {
              $scope.start = 0;
              $scope.courses = null;
  	}

  	$scope.selectSort = function(sort) {
  		$scope.sort = sort;
  		clearData();
  		$scope.loadCourseList(sort, callbackType.infinite);
  	}

  	$scope.onRefresh = function() {
  		clearData();
  		$scope.loadCourseList($scope.sort, callbackType.refresh);
  	}

  	$scope.categorySelectedListener = function(category) {
  		clearData();
           $stateParams.type = null;
           $stateParams.categoryId  =category.id;
           $scope.loadCourseList($scope.sort, callbackType.infinite);
  	}
}