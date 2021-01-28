define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    var Uploader = require('upload');
    require('common/validator-rules').inject(Validator);
    require('jquery.form');
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {
        var $form = $('#block-form');
        var $modal = $form.parents('.modal');
        var $blockHistory = $("#block-history");
        $.post($blockHistory.data('url'), function(data) {
            $blockHistory.html(data);
        });
        $modal.unbind('click.modal-pagination');
        $blockHistory.on('click', '.pagination a', function(e) {
            e.preventDefault();
            $.get($(this).attr('href'), function(html) {
                $blockHistory.html(html);
            });
            return false;
        });
        $blockHistory.on('click', '.btn-recover-content', function() {
            var html = $(this).parents('tr').find('.data-role-content').text();
            $("#blockContent").val(html);
            Notify.success(Translator.trans('admin.block.history_recover_hint','10'));
        });

    }

});