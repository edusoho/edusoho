app.controller('MyLearnController', ['$scope', 'CourseService', MyLearnController]);

function MyLearnController($scope, CourseService)
{
	var self = this;
	self.content = {
		course : {
			start : 0,
			canLoad : true,
			data : undefined
		},
		live : {
			start : 0,
			canLoad : true,
			data : undefined
		}
	};

	$scope.course = self.content.course;
	$scope.live = self.content.live;

  	self.loadDataList = function(content, serviceCallback) {
  		serviceCallback({
  			limit : 10,
			start: content.start
  		}, function(data) {

  			if (!data || data.data.length == 0) {
	    			content.canLoad = false;
	    		}

	    		content.data = content.data || [];
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
}