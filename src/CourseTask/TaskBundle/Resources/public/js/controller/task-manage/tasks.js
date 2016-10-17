define(function(require, exports, module) {
	exports.run = function() {
		$("a[data-role='delete-task']", $("#course-tasks")).click(function(){
			$.post($(this).data('url'), function(data){
				document.location.reload();
			});
		});

    };
});