function MyLearnBaseController($scope, serviceCallback)
{
	$scope.content = {
		start : 0,
		canLoad : true,
		data : []
	};

  	$scope.loadDataList = function(type) {
  		serviceCallback({
  			limit : 10,
			start: $scope.content.start,
			token : $scope.token
  		}, function(data) {
  			console.log(data);
  			if (!data || data.data.length == 0) {
	    			$scope.content.canLoad = false;
	    		}

	    		$scope.content.data = $scope.content.data.concat(data.data);
	    		$scope.content.start += data.limit;

	    		if (data.total && $scope.content.start >= data.total) {
	    			$scope.content.canLoad = false;
	    		}
	    		$scope.$broadcast('scroll.infiniteScrollComplete');
  		});
  	}

  	$scope.canLoadMore = function(type) {
  		return $scope.content.canLoad;
  	};

  	$scope.loadMore = function(type){
  		$scope.loadDataList(type);
  	};
}

app.controller('MyLearnCourseController', ['$scope', 'CourseService', MyLearnCourseController]);

function MyLearnCourseController($scope, CourseService)
{
	console.log("MyLearnCourseController");
	this.__proto__  = new MyLearnBaseController($scope, CourseService.getLearningCourse);
}

app.controller('MyLearnLiveController', ['$scope',  'CourseService', MyLearnLiveController]);
function MyLearnLiveController($scope, CourseService)
{
	console.log("MyLearnLiveController");
	this.__proto__  = new MyLearnBaseController($scope, CourseService.getLiveCourses);
}

app.controller('MyLearnClassRoomController', ['$scope', 'CourseService', MyLearnClassRoomController]);
function MyLearnClassRoomController($scope, ClassRoomService)
{
	console.log("MyLearnClassRoomController");
	this.__proto__  = new MyLearnBaseController($scope, ClassRoomService.myClassRooms);	
}