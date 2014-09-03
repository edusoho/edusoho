define(function(require, exports, module) {
	
	var $btn = $('#exit-course-learning');

	$btn.click(function(){

		if (!confirm('确定要退出学习？')) {
            return ;
		}

        var goto = $(this).data('goto');
        $.post($(this).data('url'), function(res){
            window.location.href = goto;
        });

    });
});