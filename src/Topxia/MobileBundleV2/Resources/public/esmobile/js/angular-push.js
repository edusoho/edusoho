(function(window, angular, undefined) {
	'use strict';

	angular.module('ngPushServer', ['ng'])
	.service('push', function() {
		this.toggleMenu  = function() {
				sidebarMenuEffects.toggle();
			}
	});
})(window, window.angular);