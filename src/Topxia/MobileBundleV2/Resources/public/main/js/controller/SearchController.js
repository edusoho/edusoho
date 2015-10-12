app.controller('SearchController', ['$scope', 'CourseService', 'ClassRoomService', 'cordovaUtil', '$timeout', SearchController]);

function SearchController($scope, CourseService, ClassRoomService, cordovaUtil, $timeout)
{
	$scope.search = "";
	var self = this;
	
	$scope.focusSearchInput = function() {
		$('.ui-searchbar-wrap').addClass('focus');
        		$('.ui-searchbar-input input').focus();
        		esNativeCore.showKeyInput();
	};

	$scope.inputKeyPress = function($event) {
		if ($event.keyCode == 13 && $scope.search.length > 0) {
			self.search();
		}
	};

	$scope.seach = function() {
		if ($scope.search.length == 0) {
			cordovaUtil.closeWebView();
			return;
		}
		self.search();
	};

	this.initSearch = function() {
		self.content = {
			course : {
				start : 0,
				canLoad : true,
				total : 0,
				data : undefined
			},
	    classroom : {
	      start : 0,
	      canLoad : true,
	      total : 0,
	      data : undefined
	    }
		};

		$scope.searchCourse = self.content.course;
		$scope.searchClassRoom = self.content.classroom;
	};

	this.search = function() {
		$scope.showLoad();
		self.initSearch();
		self.loadSearchCourses(self.content.course);
		self.loadSearchClassrooms(self.content.classroom);
	};

	this.loadSearchCourses = function(content) {
          CourseService.searchCourse({
            limit : 5,
            start: content.start,
            search : $scope.search
          }, function(data) {
                    $scope.hideLoad();
                    var length  = data ? data.data.length : 0;
                    content.canLoad = ! (! data || length < 10);

                    content.data = content.data || [];
                    for (var i = 0; i < length; i++) {
                      content.data.push(data.data[i]);
                    };

                    content.total = data.total;
                    content.start += data.limit;
                    $scope.searchCourse = content;
                    $scope.$apply();
          });
  };

  this.loadSearchClassrooms = function(content) {
  	ClassRoomService.search({
  		limit : 5,
  		start : content.start,
  		title : $scope.search
  	}, function(data) {
  		$scope.hideLoad();
      var length  = data ? data.data.length : 0;
      content.canLoad = ! (! data || length < 10);

      content.data = content.data || [];
      for (var i = 0; i < length; i++) {
        content.data.push(data.data[i]);
      };

      content.total = data.total;
      content.start += data.limit;
      $scope.searchClassRoom = content;
      $scope.$apply();
  	});
  }
}