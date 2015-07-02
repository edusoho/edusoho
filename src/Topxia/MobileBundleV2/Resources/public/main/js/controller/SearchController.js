app.controller('SearchController', ['$scope', 'ServcieUtil', SearchController]);

function SearchController($scope, ServcieUtil)
{
	$scope.search = "";
	var self = this;
	var CourseService = ServcieUtil.getService("CourseService");
	$scope.showSearch = function() {
		$('.ui-searchbar-wrap').addClass('focus');
        		$('.ui-searchbar-input input').focus();
	}

	$scope.seach = function() {
		if ($scope.search.length == 0) {
			window.history.back();
			return;
		}
		$scope.start = 0;
		$scope.searchDatas = undefined;
		self.loadSearchData();
	}

	$scope.canLoad = false;
    	$scope.start = $scope.start || 0;

	this.loadSearchData = function() {
             $scope.showLoad();
              CourseService.searchCourse({
                limit : 10,
                start: $scope.start,
                search : $scope.search
              }, function(data) {
                        $scope.hideLoad();
                        var length  = data ? data.data.length : 0;
                        $scope.canLoad = ! (! data || length < 10);

                        $scope.searchDatas = $scope.searchDatas || [];
                        for (var i = 0; i < length; i++) {
                          $scope.searchDatas.push(data.data[i]);
                        };

                        $scope.start += data.limit;
                        $scope.$apply();
              });

              $scope.loadMore = function(){
	            if (! $scope.canLoad) {
	              return;
	            }
	           setTimeout(function() {
	              self.loadSearchData();
	           }, 200); 
	  };
      }
}