define(function(require, exports, module) {

    var Lazyload = require('echo.js');
    require('jquery.lavalamp');
    var Widget = require('widget');

    exports.run = function() {

    	Lazyload.init();

    	var $searchResult = $("#search-result");

		var SearchPage = Widget.extend({
			events: {
				'click .js-btn-clear':  'OnBtnClear'
			},
			setup: function() {
				Lazyload.init();

				$("#search-input-group").on('input propertychange','input',function(){
					var $this =$(this);
					var btnClear = $this.siblings('.js-btn-clear');

					if ($this.val()) {
						btnClear.show();
					}else {
						btnClear.hide();
					}
				})

				$("#search-nav-tabs").lavaLamp();
			},
			OnBtnClear: function(e) {
				var $this = $(e.currentTarget);

				$this.siblings('input').val("").end().hide();
			}
			
		});

		var searchPage = new SearchPage({
			element: 'body'
		})

    };

});