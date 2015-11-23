define(function(require, exports, module) {

    var Lazyload = require('echo.js');
    require('jquery.lavaTab');
    var Widget = require('widget');

    exports.run = function() {

    	Lazyload.init();

    	var $searchResult = $("#search-result");

		var SearchPage = Widget.extend({
			events: {
				'click .js-btn-clear':  'OnBtnClear',
				'click .pagination>li>a': 'Onpagination',
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

				var that = this;
				$("#search-nav-tabs").lavaTab({
		        	fx: "backout",
		        	speed: 700,
		        	click: function(evt, currentItem, callback) {
						var $currentItem = $(currentItem);
						if ($currentItem.hasClass('active')) {
							return;
						}
						//callback && callback(currentItem);
						//that._getSearchData($currentItem);
					}
		        });
			},
			// ajax共同方法
			_searchAjax: function(url,type) {
				$.ajax({
	        		url: url,
	        		type: 'GET',
	        		beforeSend: function(){
						$searchResult.html('<div class="loading"><i class="fa fa-spinner fa-pulse"></i></div>');
					},
	        	}).done(function(html) {

	        		$searchResult.html(html);

	        		switch(type) {
	        			case 'course':
	        				Lazyload.init();
	        				break;
	    				case 'teacher':
	    					require('topxiawebbundle/util/follow-btn');
	    					break;
	        		}

	        	}).fail(function() {

	        		$searchResult.html('<div class="empty">请求失败！</div>');
	        	});
			},
			_getSearchData: function($this) {
				var url = $this.find("a").data("url");
	        	var type = $this.find("a").data("type");

	        	this._searchAjax(url,type);
			},
			OnBtnClear: function(e) {
				var $this = $(e.currentTarget);

				$this.siblings('input').val("").end().hide();
			},
			// 分页
			Onpagination: function(e) {
				e.preventDefault();
				var $this = $(e.currentTarget);
				var url = $this.attr("herf");

				//this._searchAjax(url,null);
			}
			
		});

		var searchPage = new SearchPage({
			element: 'body'
		})

    };

});