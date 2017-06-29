define(function(require, exports, module) {
    require('jquery.sortable');
    var Sticky = require('sticky');
    var Notify = require('common/bootstrap-notify');


	exports.run = function() {

        $('tbody').on('click', '.delete-btn', function() {
            if (!confirm(Translator.trans('确定要删除该分类展示吗？'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(response) {
                
                    Notify.success(Translator.trans('删除成功!'));
                    setTimeout(function(){
                        window.location.reload();
                    }, 500);
                
            }, 'json');
        });

	};

});