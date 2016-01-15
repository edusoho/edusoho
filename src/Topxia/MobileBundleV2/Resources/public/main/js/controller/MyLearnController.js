app.controller('MyLearnController', ['$scope', 'CourseService', 'ClassRoomService', MyLearnController]);

function MyLearnController($scope, CourseService, ClassRoomService)
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
		},
    classroom : {
      start : 0,
      canLoad : true,
      data : undefined
    }
	};

	$scope.course = self.content.course;
	$scope.live = self.content.live;
  $scope.classroom = self.content.classroom

  	self.loadDataList = function(content, serviceCallback, successCallback) {
      $scope.showLoad();
  		serviceCallback({
  			limit : 10,
			start: content.start
  		}, function(data) {

        $scope.hideLoad();
        if (successCallback) {
          successCallback();
        }
  			if (!data || data.data.length == 0) {
    			content.canLoad = false;
    		}

    		content.data = content.data || [];
    		content.data = content.data.concat(data.data);
    		content.start += data.limit;

    		if (data.limit > data.data.length) {
    			content.canLoad = false;
    		}
    		if (data.total && content.start >= data.total) {
    			content.canLoad = false;
    		}
  		});
  	}

  	$scope.canLoadMore = function(type) {
  		return self.content[type].canLoad;
  	};

  	$scope.loadMore = function(type, successCallback){
  		switch (type) {
  			case "course": 
  				self.loadDataList(self.content.course, CourseService.getLearningCourse, successCallback);
  				break;
  			case "live": 
  				self.loadDataList(self.content.live, CourseService.getLiveCourses, successCallback);
  				break;
        case "classroom":
          self.loadDataList(self.content.classroom, ClassRoomService.getLearnClassRooms, successCallback);
          break;
  		}
  	};

  	$scope.loadCourses = function() {
  		self.loadDataList(self.content.course, CourseService.getLearningCourse);
  	}

  	$scope.loadLiveCourses = function() {
  		self.loadDataList(self.content.live, CourseService.getLiveCourses);
  	}

    $scope.loadClassRooms = function() {
      self.loadDataList(self.content.classroom, ClassRoomService.getLearnClassRooms);
    }

  	$scope.loadCourses();
}