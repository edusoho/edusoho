app.controller('CourseListController', ['$scope', '$stateParams', 'AppUtil', 'CourseUtil', 'CourseService', 'CategoryService', CourseListController]);


function CourseListController($scope, $stateParams, AppUtil, CourseUtil, CourseService, CategoryService)
{
	$scope.courses = [];
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

	    		$scope.$broadcast(callbackType);
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
  		$scope.loadCourseList(sort, callbackType.infinite);
  	}

  	$scope.onRefresh = function() {
  		clearData();
  		$scope.loadCourseList($scope.sort, callbackType.refresh);
  	}

  	$scope.categorySelectedListener = function(category) {
  		clearData();
           $stateParams.categoryId  =category.id;
  		$scope.loadCourseList($scope.sort, callbackType.infinite);
  	}

}