var appControllers = angular.module('AppControllers', []);

appControllers.controller('ListController', ['$scope', '$http', ListController]);
appControllers.controller('DetailController', ['$scope', '$http', '$routeParams', '$sce', DetailController]);

function ListController($scope, $http)
{
	$scope.limit = 10;
	$scope.isShowLoadMore = true;
  	$scope.loadMore = function(){
  		var el=Zepto.loading({
		        content:'加载中...',
		});
		queryArticelList(function(){
			el.loading("hide");
		});
  	};

  	var queryArticelList = function(success){
  		$http.post('/mapi_v2/articleApp/list', {start:$scope.start}).success(function(data) {
			if (! $scope.articles) {
				$scope.articles = [];
			}

			if (!data || data.length < $scope.limit) {
				$scope.isShowLoadMore = false;
			}
			$scope.data = data;
	    		$scope.articles = $scope.articles.concat(data);
	    		if (success) {
	    			success();
	    		}
	    		$scope.start += $scope.limit;
	  	});
  	}

  	queryArticelList();
}


function DetailController($scope, $http, $routeParams, $sce)
{
      var articleId = $routeParams.id;
	$http.get('/mapi_v2/articleApp/detail/' + articleId).success(function(data) {
    		$scope.article = data.article;
  	});
}