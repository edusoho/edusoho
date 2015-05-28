app.controller('CourseListController', ['$scope', '$stateParams', 'AppUtil', 'CourseUtil', 'CourseService', 'CategoryService', CourseListController]);
app.controller('CourseController', ['$scope', '$stateParams', 'CourseService', 'AppUtil', 'LessonService', CourseListController]);
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

function CourseController($scope, $stateParams, CourseService, AppUtil, LessonService, $ionicScrollDelegate)
{
    CourseService.getCourse({
      courseId : $stateParams.courseId,
      token : $scope.token
    }, function(data) {
      $scope.ratingArray = AppUtil.createArray(5);
      $scope.vipLevels = data.vipLevels;
      $scope.course = data.course;
    });

    LessonService.getCourseLessons({
      courseId : $stateParams.courseId,
      token : $scope.token
    }, function(data) {
      $scope.lessons = data.lessons;
    });

    CourseService.getReviews({
      courseId : $stateParams.courseId,
      token : $scope.token,
      limit : 1
    }, function(data) {
      $scope.reviews = data.data;
    });

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