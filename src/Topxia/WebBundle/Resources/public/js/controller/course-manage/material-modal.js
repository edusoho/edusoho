define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('jquery.form');

    var FileChooser = require('../widget/file/file-chooser');

    exports.run = function() {

        var materialChooser = new FileChooser({
            element: '#material-file-chooser'
        });


        var $form = $("#course-material-form");

        $form.on('click', '.delete-btn', function(){
            var $btn = $(this);
            if (!confirm('真的要删除该资料吗？')) {
                return ;
            }

            $.post($btn.data('url'), function(){
                $btn.parents('.list-group-item').remove();
                Notify.success('资料已删除');
            });
        });

    };

});