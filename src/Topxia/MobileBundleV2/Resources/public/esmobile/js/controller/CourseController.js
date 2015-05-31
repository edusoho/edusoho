app.controller('CourseListController', ['$scope', '$stateParams', 'AppUtil', 'CourseUtil', 'CourseService', 'CategoryService', CourseListController]);
app.controller('CourseController', ['$scope', '$stateParams', 'ServcieUtil', 'AppUtil', '$ionicScrollDelegate', '$state', CourseListController]);
app.controller('CourseDetailController', ['$scope', '$stateParams', 'CourseService', CourseDetailController]);

function CourseDetailController($scope, $stateParams, CourseService)
{
  CourseService.getCourse({
      courseId : $stateParams.courseId,
      token : $scope.token
    }, function(data) {
      $scope.course = data.course;
    });
}

function CourseController($scope, $stateParams, ServcieUtil, AppUtil, $ionicScrollDelegate, $state)
{
    $scope.showLoad();

    var CourseService = ServcieUtil.getService("CourseService");
    var LessonService = ServcieUtil.getService("LessonService");

    CourseService.getCourse({
      courseId : $stateParams.courseId,
      token : $scope.token
    }, function(data) {
      console.log(data);
      $scope.ratingArray = AppUtil.createArray(5);
      $scope.vipLevels = data.vipLevels;
      $scope.course = data.course;
      $scope.isFavorited = data.userFavorited;
      $scope.discount = data.discount;

      if (data.member) {
        $scope.learnProgress = (data.member.learnedNum / data.course.lessonNum * 100) + "%" ;
      }
      $scope.courseView = app.viewFloder + (data.member ? "view/course_learn.html" : "view/course_no_learn.html");
      $scope.hideLoad();
      $scope.loadLessons();
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

    $scope.joinCourse = function() {
      if ($scope.course.price <= 0) {
        self.payCourse();
      } else {
        $scope.$parent.stateParams["coursePay"] = {
            course : $scope.course
        };
          
        $state.go("coursePay");
      }
      
    }

    /*
    var mainHandle = $ionicScrollDelegate.$getByHandle("mainScroll");
    var tabScroll = $ionicScrollDelegate.$getByHandle("tabScroll");

    var lastScrollTop = 0;

    $scope.xscroll = function(event, scrollTop, scrollLeft) {

      var position = mainHandle.getScrollPosition();
      var chlidPosition = tabScroll.getScrollPosition();

      if (position.top == 302 ) {
        if (scrollTop > lastScrollTop) {
          lastScrollTop = scrollTop;
          console.log("up");
          return;
        }
        if (scrollTop < lastScrollTop) {
          console.log("down");
          lastScrollTop = scrollTop;
        }

        return;
      }

      var scroll = event.srcElement.querySelector(".scroll");
      scroll.style.webkitTransform = "translate3d(0px, " + "0px, 0px) scale(1)";
      mainHandle.scrollTo(scrollLeft, scrollTop * 3);
      lastScrollTop = scrollTop;
    }
    */
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
  		CourseService.searchCourse({
  			limit : 10,
			start: $scope.start,
			categoryId : $stateParams.categoryId,
			sort : sort,
                 type : $stateParams.type
  		}, function(data) {
  			if (!data || data.data.length == 0 ) {
	    			$scope.canLoad = false;
	    		}

	    		$scope.courses = $scope.courses ? $scope.courses.concat(data.data) : data.data;
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
           $scope.loadCourseList($scope.sort, callbackType.infinite);
  	}

  	function clearData() {
  		$scope.courses = [];
  		$scope.start = 0;
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