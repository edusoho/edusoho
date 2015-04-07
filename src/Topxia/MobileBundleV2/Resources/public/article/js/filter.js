angular.module('AppFilters', []).
filter('blockStr', ['$rootScope', function($rootScope) {
	return function(content, limitTo){
		content = content.replace(/<[^>]+>/g, "");
		if (limitTo) {
			content = content.substring(0, limitTo);
		}
		return content;
	};
}])