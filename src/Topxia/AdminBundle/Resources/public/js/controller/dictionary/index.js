define(function(require, exports, module) {
    require('jquery.sortable');
    var Sticky = require('sticky');
    var Notify = require('common/bootstrap-notify');


	exports.run = function() {

        $('tbody').on('click', '.delete-btn', function() {
            if (!confirm(Translator.trans('admin.dictionary.delete_hint'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(response) {
                
                    Notify.success(Translator.trans('admin.dictionary.delete_success_hint'));
                    setTimeout(function(){
                        window.location.reload();
                    }, 500);
                
            }, 'json');
        });

	};

});