define(function(require, exports, module) {
	$('.js-exit').on('click', function(){
        var self = $(this);
        $.post($(this).data('url'), function(){
            window.location.href = self.data('go');
        });
    });
});