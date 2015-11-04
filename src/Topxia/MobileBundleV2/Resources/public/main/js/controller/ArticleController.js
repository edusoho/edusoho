app.controller('ArticleController', ['$scope', '$state', '$stateParams', 'cordovaUtil', 'ArticleService', ArticleController]);

function ArticleController($scope, $state, $stateParams, cordovaUtil, ArticleService)
{	
	var self = this;

	this.filterContent = function(content, limit) {

		content = content.replace(/<\/?[^>]*>/g,'');
		content = content.replace(/[\r\n\s]+/g,'');
		if (content.length > limit) {
	       content = content.substring(0, limit);
	    }
	    
	    return content;
	}

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
		cordovaUtil.share(
	        app.host + "/article/" + $scope.article.id, 
	        $scope.article.title, 
	        self.filterContent($scope.article.body, 20), 
	        $scope.article.picture
	    );  
	}

	$scope.redirect = function() {
		var url = [app.rootPath, "#article/", $scope.article.id ].join("");
		cordovaUtil.redirect({
			type : "news.redirect",
			fromType : "news",
			id : $scope.article.id,
			title : $scope.article.title,
			image : $scope.article.picture,
			content : self.filterContent($scope.article.body, 20),
			url : url,
			source : "self"
		});
	}
}