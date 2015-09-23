app.controller('ArticleController', ['$scope', '$state', '$stateParams', 'ArticleService', ArticleController]);

function ArticleController($scope, $state, $stateParams, ArticleService)
{	
	$scope.init = function() {
		ArticleService.getArticle({
			id : $stateParams.id
		}, function(data) {
			console.log(data);
			$scope.article = data;
		});
	};
}