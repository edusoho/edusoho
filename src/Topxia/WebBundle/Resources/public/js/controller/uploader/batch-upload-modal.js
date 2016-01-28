define(function(require, exports, module) {
    var BatchUploader = require('./batch-uploader');

    exports.run = function() {

        var $el = $('#batch-uploader');
        var esuploader = new BatchUploader({
            element: $el,
            initUrl: $el.data('initUrl'),
            finishUrl: $el.data('finishUrl'),
            uploadAuthUrl: $el.data('uploadAuthUrl')
        });


        $el.parents('.modal').on('hidden.bs.modal', function(){
            window.location.reload();
        });

    };

});