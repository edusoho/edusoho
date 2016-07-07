define(function(require, exports, module) {

    var Lazyload = require('echo.js');
    require('jquery.lavalamp');
    require('topxiawebbundle/util/follow-btn');
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

                if($("#search-input-group .form-control").val()) {
                    $(".js-btn-clear").show();
                }

				$("#search-input-group").on('input propertychange','.form-control',function(){
					var $this =$(this);
					var btnClear = $this.siblings('.js-btn-clear');

					if ($this.val()) {
						btnClear.show();
					}else {
						btnClear.hide();
					}
				})
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