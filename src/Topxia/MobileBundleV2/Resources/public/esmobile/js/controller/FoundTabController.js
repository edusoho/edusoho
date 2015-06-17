app.controller('FoundCourseController', ['$scope', 'SchoolService', FoundCourseController]);

function FoundCourseController($scope, SchoolService)
{
	console.log("FoundCourseController");
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

app.controller('FoundLiveController', ['$scope', 'SchoolService', 'CourseService', FoundLiveController]);

function FoundLiveController($scope, SchoolService, CourseService)
{
	console.log("FoundLiveController");

	CourseService.getAllLiveCourses({ limit : 3 }, function(data) {
		$scope.liveCourses = data.data;
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
}