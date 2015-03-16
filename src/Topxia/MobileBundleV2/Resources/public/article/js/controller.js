var appControllers = angular.module('AppControllers', []);

appControllers.controller('ListController', ['$scope', '$http', '$rootScope', ListController]);
appControllers.controller('CategoryController', ['$scope', '$timeout', '$state', CategoryController]);
appControllers.controller('DetailController', ['$scope', '$http', '$stateParams', '$sce', DetailController]);

function ListController($scope, $http, $rootScope)
{
	$scope.limit = 10;
	$scope.categoryId = 0;
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
  		$http.post(
  			'/mapi_v2/articleApp/list', 
  			{
  				start: $scope.start,
  				categoryId : $scope.categoryId 
  			}).success(function(data) {
				if (! $scope.articles) {
					$scope.articles = [];
				}

				if (data.length == 0) {
					$scope.isEmpty = true;
				}
				$scope.isShowLoadMore = !(!data || data.length < $scope.limit);
				$scope.data = data;
		    		$scope.articles = $scope.articles.concat(data);
		    		if (success) {
		    			success();
		    		}
		    		$scope.start += $scope.limit;
	  	});
  	}

  	queryArticelList(); 

  	angular.broadCast.bind("changeCategoryBroadCast", function(event, msg){
  		console.log("changeCategoryBroadCast " + msg);
  		$scope.start = 0;
  		$scope.articles = [];
  		$scope.categoryId  = msg;
  		queryArticelList();
  	});
}

function CategoryController($scope, $timeout, $state)
{
	$scope.isShowCategory = false;
	var menu = {
	      "name" : "分类",
	      "icon" : "lesson_menu_list",
	      "action" : "angular.$client.showCategory()",
	      "item" : []
	  };

	navigator.cordovaUtil.createMenu(menu);
	console.log("createMenu");
	
	angular.$client.showCategory = function(){
	  	$scope.$apply(function(){
  			$scope.isShowCategory = !$scope.isShowCategory;
  		});
	};
  	$scope.changeCategory = function(categoryId) {
  		console.log("categoryId " + categoryId);
  		$scope.isShowCategory = !$scope.isShowCategory;
  		angular.broadCast.send("changeCategoryBroadCast", categoryId);
  	};
}

function DetailController($scope, $http, $stateParams, $sce)
{
      var articleId = $stateParams.id;
	$http.get('/mapi_v2/articleApp/detail/' + articleId).success(function(data) {
    		$scope.content = data;
  	});
}