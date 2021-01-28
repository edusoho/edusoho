define(function(require, exports, module) {

	require('../widget/category-select').run('article');

	exports.run = function() {
		$("#article-table").on('click', '[data-role=trash-item]', function(){
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});

		$("#article-property-tips").popover({
			html: true,
			trigger: 'hover',
			placement: 'left',
			content: $("#article-property-tips-html").html()
		});

		$("#article-table").on('click', '[data-role=publish-item]', function(){
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});	

		$("#article-table").on('click', '[data-role=unpublish-item]', function(){
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});	

		$("#article-table").on('click', '[data-role=delete-item]', function(){
			if (!confirm(Translator.trans('admin.article.delete_hint'))) {
				return ;
			}
			$.post($(this).data('url'), function(){
				window.location.reload();
			});
		});

		$(".featured-label").on('click', function() {
			var $self = $(this);
			var span = $self.find('span');
			var spanClass = span.attr('class');
			var postUrl;

			if(spanClass == "label label-default"){
				postUrl = $self.data('setUrl');
				$.post(postUrl, function(response) {
					var labelStatus = "label label-success";
					span.attr('class',labelStatus)
				});
			}else{
				postUrl = $self.data('cancelUrl');
				$.post(postUrl, function(response) {
					var labelStatus = "label label-default";
					span.attr('class',labelStatus)
				});
			}
		});		

		$(".promoted-label").on('click', function(){

			var $self = $(this);
			var span = $self.find('span');
			var spanClass = span.attr('class');
			var postUrl = "";

			if(spanClass == "label label-default"){
				postUrl = $self.data('setUrl');
				$.post(postUrl, function(response) {
					var labelStatus = "label label-success";
					span.attr('class',labelStatus)
				});
			}else{
				postUrl = $self.data('cancelUrl');
				$.post(postUrl, function(response) {
					var labelStatus = "label label-default";
					span.attr('class',labelStatus)
				});
			}

		});		

		$(".sticky-label").on('click', function(){
		
			var $self = $(this);
			var span = $self.find('span');
			var spanClass = span.attr('class');
			var postUrl = "";
			
			if(spanClass == "label label-default"){
				postUrl = $self.data('setUrl');
				$.post(postUrl, function(response) {
					var labelStatus = "label label-success";
					span.attr('class',labelStatus)
				});
			}else{
				postUrl = $self.data('cancelUrl');
				$.post(postUrl, function(response) {
					var labelStatus = "label label-default";
					span.attr('class',labelStatus)
				});
			}
		});

		var $container = $('#aticle-table-container');
		var $table = $('#article-table');
		 require('../../util/batch-select')($container);
		 require('../../util/batch-delete')($container);
		 require('../../util/item-delete')($container);
	};

});