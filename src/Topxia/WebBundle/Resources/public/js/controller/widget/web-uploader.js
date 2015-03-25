define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    require('webuploader');

    var Uploader = Widget.extend({
        attrs: {
            maxSize:2*1024*1024,
            type: '',
            fileInput: '',
            title: '上传',
            formData: {},
            accept: {
	            title: 'Images',
	            extensions: 'gif,jpg,jpeg,png',
	            mimeTypes: 'image/*'
	        },
	        uploader: null
        },

        events: {
            
        },

        setup: function() {
		    var uploader = WebUploader.create({
		        swf: require.resolve("webuploader").match(/[^?#]*\//)[0] + "Uploader.swf",
		        server: this.element.data('uploadUrl'),
		        pick: this.element,
		        title:'aaa',
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

		    this.set("uploader", uploader);
		},

		upload: function(){
			alert(11);
			this.get("uploader").upload();
		}

    });

	module.exports = Uploader;

});