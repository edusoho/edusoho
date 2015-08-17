app.controller('FoundCourseController', ['$scope', 'SchoolService', '$state', FoundCourseController]);

function FoundCourseController($scope, SchoolService, $state)
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

app.controller('FoundLiveController', ['$scope', 'SchoolService', FoundLiveController]);

function FoundLiveController($scope, SchoolService)
{
	console.log("FoundLiveController");

	SchoolService.getLiveRecommendCourses({ limit : 3 } , function(data) {
		$scope.liveRecommedCourses = data.data;
	});

	SchoolService.getLiveLatestCourses( {  limit : 3,  },  function(data) {
		$scope.liveLatestCourses = data.data;
	});
}

app.controller('FoundClassRoomController', ['$scope', 'ClassRoomService', 'ClassRoomUtil', FoundClassRoomController]);

function FoundClassRoomController($scope, ClassRoomService, ClassRoomUtil)
{
	console.log("FoundClassRoomController");

  	ClassRoomService.getRecommendClassRooms({ limit : 3 }, function(data) {
  		$scope.recommendClassRooms = ClassRoomUtil.filterClassRooms(data.data);
  	});

  	ClassRoomService.getLatestClassrooms({ limit : 3 }, function(data) {
  		$scope.latestClassrooms = ClassRoomUtil.filterClassRooms(data.data);
  	});


}