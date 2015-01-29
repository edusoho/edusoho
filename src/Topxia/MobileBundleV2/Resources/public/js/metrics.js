App._Metrics = function (window, App) {
	var analyticsEnabled = false;

	App.enableGoogleAnalytics = function () {
		enableGoogleAnalytics();
	};

	return {
		watchPage : watchPage
	};



	function enableGoogleAnalytics () {
		analyticsEnabled = true;
	}

	function addPageView (pageName, pageID) {
		if ( !analyticsEnabled ) {
			return;
		}

		var pathname = '/' + pageName;
		if (typeof pageID !== 'undefined') {
			pathname += '/' + pageID;
		}

		if (typeof window.ga === 'function') {
			window.ga('send', 'pageview', pathname);
			return;
		}

		if ( !window._gaq ) {
			window._gaq = [];
		}
		if (typeof window._gaq.push === 'function') {
			window._gaq.push([
				'_trackPageview' ,
				pathname
			]);
		}
	}

	function watchPage (page, pageName, pageArgs) {
		var data;

		if ((typeof pageArgs === 'object') && (typeof pageArgs.id !== 'undefined')) {
			data = pageArgs.id + '';
		}

		page.addEventListener('appShow', function () {
			addPageView(pageName, data);
		}, false);
	}
}(window, App);
