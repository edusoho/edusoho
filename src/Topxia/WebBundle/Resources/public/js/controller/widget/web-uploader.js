define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    require('webuploader');

    var WebUploader = Widget.extend({
        attrs: {
            maxSize:2*1024*1024,
            type: '',
            fileInput: '',
            title: '',
            formData: {},
            accept: {
	            title: 'Images',
	            extensions: 'gif,jpg,jpeg,png',
	            mimeTypes: 'image/*'
	        }
        },

        events: {
            
        },

        setup: function() {

		    var uploader = WebUploader.create({
		        swf: require.resolve("webuploader").match(/[^?#]*\//)[0] + "Uploader.swf",
		        server: this.element.data('uploadUrl'),
		        pick: this.element,
		        formData: $.extend(this.formData, {'_csrf_token': $('meta[name=csrf-token]').attr('content') }),
		        accept: this.accept
		    });

		    uploader.on( 'fileQueued', function( file ) {
		        Notify.info('正在上传，请稍等！', 0);
		        uploader.upload();
		    });

		    uploader.on( 'uploadSuccess', function( file, response ) {
		        var result = '[image]' + response.hashId + '[/image]';
		        var $input = $($('#item-upload-' + model.id).data('target'));
		        $input.val($input.val() + result);
		        Notify.success('上传成功！', 1);
		    });

		    uploader.on( 'uploadError', function( file, response ) {
		        Notify.danger('上传失败，请重试！');
		    });

		}
    });

});