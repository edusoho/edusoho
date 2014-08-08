define(function(require, exports, module) {

    var BaseChooser = require('./base-chooser-4');
    require('jquery.perfect-scrollbar');
    var Notify = require('common/bootstrap-notify');

    var PPTChooser = BaseChooser.extend({
        attrs: {
            uploaderSettings: {
                file_types : "*.ppt;*.pptx",
                file_size_limit : "100 MB",
                file_types_description: "PPT文件"
            },
            preUpload: function(uploader, file) {
                var data = {};
                $.ajax({
                    url: this.element.data('paramsUrl'),
                    async: false,
                    dataType: 'json',
                    data: data, 
                    cache: false,
                    success: function(response, status, jqXHR) {
                        uploader.setUploadURL(response.url);
                        uploader.setPostParams(response.postParams);
                    },
                    error: function(jqXHR, status, error) {
                        Notify.danger('请求上传授权码失败！');
                    }
                });
            }
        },
        
        setup: function() {
            PPTChooser.superclass.setup.call(this);
            $('#disk-browser-ppt').perfectScrollbar({wheelSpeed:50});
        }

    });

    module.exports = PPTChooser;

});


