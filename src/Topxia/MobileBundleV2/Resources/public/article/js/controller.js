var appControllers = angular.module('AppControllers', []);

appControllers.controller('ListController', ['$scope', '$http', '$rootScope', ListController]);
appControllers.controller('CategoryController', ['$scope', '$timeout', '$state', CategoryController]);
appControllers.controller('DetailController', ['$scope', '$http', '$stateParams', DetailController]);

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
  		var el=Zepto.loading({
		        content:'加载中...',
		});
  		$http.post(
  			'/mapi_v2/articleApp/list', 
  		{
  			start: $scope.start,
  			categoryId : $scope.categoryId 
  		}).success(function(data) {
  			el.loading("hide");
			if (! $scope.articles) {
				$scope.articles = [];
			}

			$scope.isEmpty = data.length == 0;
			$scope.isShowLoadMore = !(!data || data.length < $scope.limit);
			$scope.data = data;
	    		$scope.articles = $scope.articles.concat(data);
	    		if (success) {
	    			success();
	    		}
	    		$scope.start += $scope.limit;
	  	}).error(function(){
	  		el.loading("hide");
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
  	$scope.changeCategory = function(categoryId, title) {
  		console.log("categoryId " + categoryId);
  		document.title = title ? "网校资讯 - " + title : "网校资讯";
  		$scope.isShowCategory = !$scope.isShowCategory;
  		angular.broadCast.send("changeCategoryBroadCast", categoryId);
  	};
}

function DetailController($scope, $http, $stateParams)
{
      var articleId = $stateParams.id;
	$http.get('/mapi_v2/articleApp/detail/' + articleId).success(function(data) {
    		$scope.content = data.content;
    		document.title = data.title;
  	});
}