define(function(require, exports, module) {

    exports.run = function() {
    	$('.modal').on('click', '[data-toggle=form-submit]', function(e) {
            e.preventDefault();
            $($(this).data('target')).submit();
        });
    };

});