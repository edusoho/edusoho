define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('jquery.form');
    var WebUploader = require('edusoho.webuploader');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var uploader = new WebUploader({
            element: '#article-upload-btn',
        });

        uploader.on('uploadSuccess', function(file, response ) {
            var url = $("#article-upload-btn").data("gotoUrl");
            Notify.success(Translator.trans('admin.article.picture_upload_success_hint'), 1);

            $('#modal').load(url);
        });

       
    };

});