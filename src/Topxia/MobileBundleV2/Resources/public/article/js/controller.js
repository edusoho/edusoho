var appControllers = angular.module('AppControllers', []);

appControllers.controller('ListController', ['$scope', '$http', '$state', '$stateParams', ListController]);
appControllers.controller('CategoryController', ['$scope', '$http', '$state', CategoryController]);
appControllers.controller('DetailController', ['$scope', '$http', '$stateParams', DetailController]);

function ListController($scope, $http, $state, $stateParams)
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

  	$scope.showDetail = function(id){
  		angular.broadCast.send("showDetailBroadCast", 
  			{ "id" : id}
  		);
  	};

  	var queryArticelList = function(success){
  		document.title = $scope.categoryName ? "网校资讯 - " + $scope.categoryName  : "网校资讯";
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
  		$scope.categoryId  = msg.categoryId;
  		$scope.categoryName  = msg.categoryName;
  		queryArticelList();
  	});
}

function CategoryController($scope, $http, $state)
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
  		angular.broadCast.send("changeCategoryBroadCast", 
  			{ "categoryId" : categoryId, "categoryName" : categoryName}
  		);
  	};
}

function DetailController($scope, $http, $stateParams)
{
	$scope.showMode = "";
	$scope.detailContentHeight = "0px";
	angular.broadCast.bind("showDetailBroadCast", function(event, msg){
  		console.log("showDetailBroadCast " + msg);
  		var articleId = msg.id;
	      navigator.cordovaUtil.createMenu();
	      $scope.showMode = "ng-enter";
		$scope.detailContentHeight = document.body.clientHeight + "px";
		$http.get('/mapi_v2/articleApp/detail/' + articleId).success(function(data) {
	    		$scope.content = data.content;
	    		document.title = data.title;
	  	});
  	});
}