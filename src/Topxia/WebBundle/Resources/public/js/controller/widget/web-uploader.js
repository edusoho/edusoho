define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    require('webuploader');

    var Uploader = Widget.extend({
        attrs: {
            fileSizeLimit: 2*1024*1024,
            type: '',
            fileInput: '',
            title: '上传',
            formData: {},
            accept: {
	            title: 'Images',
	            extensions: 'gif,jpg,jpeg,png',
	            mimeTypes: 'image/*'
	        },
	        uploader: null,
	        fileVal: 'file'
        },

        events: {
            'click' : "onClick"
        },

        setup: function() {
        	var self = this;
        	var formData = $.extend(self.get("formData"), {token: self.element.data("uploadToken")});

		    var uploader = WebUploader.create({
		        swf: require.resolve("webuploader").match(/[^?#]*\//)[0] + "Uploader.swf",
		        server: '/file/upload',
		        pick: {
		        	id:this.element,
		        	multiple:false
		        },
		        formData: $.extend(formData, {'_csrf_token': $('meta[name=csrf-token]').attr('content') }),
		        accept: this.get("accept"),
				auto: true,
				fileNumLimit: 1,
				fileSizeLimit: this.get("fileSizeLimit")
		    });

		    uploader.on( 'fileQueued', function( file ) {
		    	
		    });

		    uploader.on( 'uploadSuccess', function( file, response ) {
		        self.trigger("uploadSuccess", file, response);
		    });

		    uploader.on( 'uploadError', function( file, response ) {
		        Notify.danger('上传失败，请重试！');
		    });

		    uploader.on('error', function(type){
		    	switch(type) {
			    	case "Q_EXCEED_SIZE_LIMIT":
			    		Notify.danger('文件过大，请上传较小的文件！');
			    		break;
		    		case "Q_EXCEED_NUM_LIMIT":
		    			Notify.danger('添加的文件数量过多！');
			    		break;
			    	case "Q_TYPE_DENIED":
		    			Notify.danger('文件类型错误！');
			    		break;
		    	}
		    });

		    this.set("uploader", uploader);
		},

		onClick: function(){
			this.get("uploader").upload();
		}

    });

	module.exports = Uploader;

});