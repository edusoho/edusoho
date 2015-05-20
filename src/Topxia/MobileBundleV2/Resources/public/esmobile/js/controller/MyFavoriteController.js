app.controller('MyFavoriteController', ['$scope', 'httpService', MyFavoriteController]);

function MyFavoriteController($scope, CourseService, CourseUtil)
{
	console.log("MyFavoriteController");
	$scope.data  = CourseUtil.getFavoriteListTypes();

  	$scope.loadDataList = function(type) {
  		var dataList = $scope.data[type];
  		CourseService.getFavoriteCoruse(
  			dataList.url,
  			{
	  			limit : 10,
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
		    		$scope.$broadcast('scroll.infiniteScrollComplete');
  			}
  		);
  	}

  	$scope.canLoadMore = function(type) {
  		return $scope.data[type].canLoad;
  	};

  	$scope.loadMore = function(type){
  		$scope.loadDataList(type);
  	};
}