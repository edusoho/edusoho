app.controller('ArticleController', ['$scope', '$state', '$stateParams', 'cordovaUtil', 'ArticleService', ArticleController]);

function ArticleController($scope, $state, $stateParams, cordovaUtil, ArticleService)
{	
	$scope.init = function() {
		$scope.showLoad();
		ArticleService.getArticle({
			id : $stateParams.id
		}, function(data) {
			$scope.hideLoad();
			$scope.article = data;
		});
	};

	$scope.refresh = function(popScope) {
		$scope.init();
		popScope.isShowMenuPop = !popScope.isShowMenuPop;
	}

	$scope.share = function() {
		var about = $scope.article.body;
	    if (about.length > 20) {
	       about = about.substring(0, 20);
	    }
		cordovaUtil.share(
        app.host + "/article/" + $scope.article.id, 
        $scope.article.title, 
        about, 
        $scope.article.picture
      );  
	}
}