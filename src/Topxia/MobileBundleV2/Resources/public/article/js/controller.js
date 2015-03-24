var appControllers = angular.module('AppControllers', []);

appControllers.controller('ListController', ['$scope', '$http', '$ionicLoading', 'broadCast', ListController]);
appControllers.controller('CategoryController', ['$scope', '$http', 'broadCast', CategoryController]);
appControllers.controller('DetailController', ['$scope', '$http', '$stateParams', DetailController]);

function ListController($scope, $http, $ionicLoading, broadCast)
{
	$scope.limit = 10;
	$scope.categoryId = 0;
	$scope.start = $scope.start | 0;
	$scope.isShowLoadMore = false;

  	$scope.loadMore = function(){
  		$ionicLoading.show({
		        template:'加载中...',
		});
		queryArticelList(function(){
			$ionicLoading.hide();
		});
  	};

  	var queryArticelList = function(success){
  		document.title = $scope.categoryName ? "网校资讯 - " + $scope.categoryName  : "网校资讯";
  		$ionicLoading.show({
		        template:'加载中...',
		});
  		$http.post(
  			'/mapi_v2/articleApp/list', 
  		{
  			start: $scope.start,
  			categoryId : $scope.categoryId 
  		}).success(function(data) {
			$ionicLoading.hide();
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
			$ionicLoading.hide();
	  	});
  	}

  	queryArticelList(); 

  	broadCast.bind("changeCategoryBroadCast", function(event, msg){
  		console.log("changeCategoryBroadCast " + msg);
  		$scope.start = 0;
  		$scope.articles = [];
  		$scope.categoryId  = msg.categoryId;
  		$scope.categoryName  = msg.categoryName;
  		queryArticelList();
  	});
}

function CategoryController($scope, $http, broadCast)
{
	$scope.isShowCategory = false;
	$http.get('/mapi_v2/articleApp/category').success(function(data) {
    		$scope.categoryTree = data;
  	});

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

  	$scope.changeCategory = function(categoryId, categoryName) {
  		console.log("categoryId " + categoryId);
  		$scope.isShowCategory = !$scope.isShowCategory;
  		broadCast.send("changeCategoryBroadCast", 
  			{ "categoryId" : categoryId, "categoryName" : categoryName}
  		);
  	};
}

function DetailController($scope, $http, $stateParams)
{
	var articleId = $stateParams.id;
      navigator.cordovaUtil.createMenu();
	$http.get('/mapi_v2/articleApp/detail/' + articleId).success(function(data) {
    		$scope.content = data.content;
    		document.title = data.title;
  	});
}