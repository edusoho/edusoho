app.controller('CourseListController', ['$scope', '$stateParams', 'AppUtil', 'CourseUtil', 'CourseService', 'CategoryService', CourseListController]);


function CourseListController($scope, $stateParams, AppUtil, CourseUtil, CourseService, CategoryService)
{
	$scope.courses = [];
	$scope.canLoad = true;
	$scope.start = $scope.start || 0;

	console.log("CourseListController");
	$scope.canLoadMore = function() {
  		return $scope.canLoad;
  	};

  	$scope.loadMore = function(){
  		$scope.loadCourseList($stateParams.sort);
  	};

  	$scope.loadCourseList = function(sort) {
  		CourseService.getCourses({
  			limit : 10,
			start: $scope.start,
			categoryId : $stateParams.categoryId,
			sort : sort
  		}, function(data) {
  			if (!data || data.data.length == 0 ) {
	    			$scope.canLoad = false;
	    		}

	    		$scope.courses = $scope.courses.concat(data.data);
	    		$scope.start += data.limit;

	    		$scope.$broadcast('scroll.infiniteScrollComplete');
  		});
  	}

  	$scope.courseListSorts = CourseUtil.getCourseListSorts();
  	$scope.courseListTypes = CourseUtil.getCourseListTypes();


  	CategoryService.getCategorieTree(function(data) {
		$scope.categoryTree = data;
	});

  	$scope.selectType = function(item) {
  		console.log(item);
  	}

  	function clearData() {
  		$scope.courses = [];
  		$scope.start = 0;
  	}

  	$scope.selectSort = function(sort) {
  		$scope.sort = sort;
  		clearData();
  		$scope.loadCourseList(sort);
  	}

  	$scope.onRefresh = function() {
  		clearData();
  		$scope.loadCourseList($scope.sort);
  	}

  	$scope.categorySelectedListener = function() {
  		clearData();
  		$scope.loadCourseList($scope.sort);
  	}

}