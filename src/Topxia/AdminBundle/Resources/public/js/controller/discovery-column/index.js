define(function(require, exports, module) {

    require('jquery.sortable');
    var Sticky = require('sticky');
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {

        $('#upgrade-modal').modal('show');
        
        var group = $('.table-hover tbody').sortable({
            group: 'serialization',
            containerPath: '> tr',
            itemSelector: 'tr.sort',
            placeholder: '<tr class="placeholder"/>',
            onDrop: function (item, container, _super) {
                _super(item, container);
                var $tbody = $(item).parent();
                var data = group.sortable("serialize").get();
                    $.post($tbody.data('updateSeqsUrl'), {data:data}, function(response){
                });
            }
        });

        $('tbody').on('click', '.delete-btn', function() {
            if (!confirm(Translator.trans('admin.dictionary.delete_hint'))) return false;
            var $btn = $(this);
            $.post($btn.data('url'), function(response) {
                if (response.status == 'ok') {
                    Notify.success(Translator.trans('admin.dictionary.delete_success_hint'));
                    setTimeout(function(){
                        window.location.reload();
                    }, 500);
                } else {
                    alert(Translator.trans('admin.dictionary.service_error_hint'));
                }
            }, 'json');
        });
        

    }
});