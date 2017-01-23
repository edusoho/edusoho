define(function(require, exports, module) {
	$('.cancel-refund').on('click', function(){
        var self = $(this);
        $.post($(this).data('url'), function(){
            window.location.href = self.data('go');
        });
    });
});