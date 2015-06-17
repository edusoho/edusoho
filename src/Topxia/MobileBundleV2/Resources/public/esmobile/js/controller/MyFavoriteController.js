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
}