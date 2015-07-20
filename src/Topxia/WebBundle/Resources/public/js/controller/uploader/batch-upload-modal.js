define(function(require, exports, module) {
    require('webuploader2');
    var store = require('store');
    var filesize = require('filesize');
    var Widget = require('widget');

    var ESUploader = Widget.extend({

    	uploader: null,

    	attrs: {
    		initUrl: null,
    		finishUrl: null,
    		uploadUrl: null,
    		uploadToken: null
    	},

    	setup: function() {
    		var defaults = {

    		    dnd: this.element.find('.balloon-uploader-body'),

    		    // 不压缩image
    		    resize: false,

    		    // swf文件路径
    		    swf: 'Uploader.swf',

    		    // 选择文件的按钮。可选。
    		    // 内部根据当前运行是创建，可能是input元素，也可能是flash.
    		    pick: this.element																																																																																																																																																																																																																																																																																																																						.find('.file-pick-btn') ,
    		    chunked: true,
    		    chunkSize: 1024000,
    		    chunkRetry: 2,
    		    threads: 1,
    		    formData: {

    		    }
    		};

    		this._initUploaderHook();
    		var uploader = this.uploader = WebUploader.create(defaults);
    		this._registerUploaderEvent(uploader);

    		this.element.find('.start-upload-btn').on('click', function(){
    			uploader.upload();
    		});
    	},

    	_registerUploaderEvent: function(uploader) {
    		var self = this;
    		var $uploader = this.element;
    		// 当有文件添加进来的时候
    		uploader.on('fileQueued', function(file) {
    		    $uploader.find('.balloon-nofile').remove();
    		    var $list =$uploader.find('.balloon-filelist ul');
    		    $list.append(
    		        '<li id="' + file.id + '">' +
    		        '  <div class="file-name">' + file.name + '</div>' +
    		        '  <div class="file-size">' + filesize(file.size) + '</div>' +
    		        '  <div class="file-status">待上传</div>' +
    		        '  <div class="file-progress"><div class="file-progress-bar" style="width: 0%;"></div></div>' +
    		        '</li>'
    		    );
    		});

    		// 文件上传过程中创建进度条实时显示。
    		uploader.on('uploadProgress', function(file, percentage) {
    		    var $li = $('#' + file.id);
    		    percentage = (percentage * 100).toFixed(2) + '%';
    		    $li.find('.file-status').html(percentage);
    		    $li.find('.file-progress-bar').css('width', percentage);
    		});

    		uploader.on('uploadSuccess', function(file) {
    		    var $li = $('#' + file.id);
    		    $li.find('.file-status').html('已上传');
    		    $li.find('.file-progress-bar').css('width', '0%');

    		    $.ajax(startUrl, {
    		    	type: 'POST',
    		    	data: {fileId:file.gid},
    		    	dataType: 'json',
    		    	headers: {
    		    		'Upload-Token': response.postData.token
    		    	},
    		    	success: function() {
    		    		console.log('finished');
    		    	}
    		    });

    		});

    		uploader.on('uploadComplete', function(file) {
    		    console.log('upload complete');
    		});

    		uploader.on('uploadAccept', function(object, ret) {
    		    console.log('uploadAccept', object, ret);
    		    store.set('file_' + object.file.hash, object.chunk);

    		});


    		uploader.on('uploadStart', function(file) {
    		    console.log('uploadStart');
    		});

    		uploader.on('uploadBeforeSend', function(object, data, headers) {
    		    console.log('uploadBeforeSend', object);
    		    data.file_gid = object.file.gid;
    		    data.chunk_number = object.chunk +1;
    		    headers['Upload-Token'] = self.get('uploadToken');
    		});
    	},

    	_initUploaderHook: function() {
    		var self = this;
    		WebUploader.Uploader.register({
    		    'before-send-file': 'preupload',
    		    'before-send' : 'checkchunk',
    		    'after-send-file': 'finishupload',
    		}, {
    		    preupload: function(file) {
    		        var deferred = WebUploader.Deferred();

    		        self._makeFileHash(file).done(function(hash) {
    		            file.hash = hash;
    		            var params = {
    		                fileName: file.name,
    		                fileSize: file.size,
    		                fileHash: hash,
    		                convertor: '',
    		                convertParams: {'qulity': 'low'}
    		            }

    		            $.support.cors = true;

    		            $.post(self.get('initUrl'), params, function(response) {
    		            	console.log('init',response);
    		                file.gid = response.globalId;
    		                file.globalId = response.globalId;

    		                self.set('uploadToken', response.postData.token);
    		                self.set('uploadUrl', response.uploadUrl);
    		                self.uploader.option('server', response.uploadUrl + '/chunks');

    		                var startUrl = response.uploadUrl + '/chunks/start';
    		                var postData = {file_gid:file.globalId, file_size: file.size, file_name:file.name};

    		                $.ajax(startUrl, {
    		                	type: 'POST',
    		                	data: postData,
    		                	dataType: 'json',
    		                	headers: {
    		                		'Upload-Token': response.postData.token
    		                	},
    		                	success: function() {
    		                		deferred.resolve();
    		                	}
    		                });

    		            }, 'json');

    		        });

    		        return deferred.promise();
    		    },

    		    checkchunk: function(block) {
    		        console.log('checkchunk', block);
    		        var deferred = WebUploader.Deferred();

    		        // if (continueChunkNum && continueChunkNum > 0 && block.chunk < continueChunkNum) {
    		        //     console.log('秒传');
    		        //     deferred.reject();
    		        // }

    		        deferred.resolve();

    		        return deferred.promise();
    		    },

    		    finishupload: function(file) {
    		        console.log('finish-upload', file);
    		        store.remove('file_' + file.hash);
    		        var deferred = WebUploader.Deferred();

    		        $.ajax(self.get('uploadUrl') + '/chunks/finish', {
    		        	type: 'POST',
    		        	data: {file_gid:file.gid},
    		        	dataType: 'json',
    		        	headers: {
    		        		'Upload-Token': self.get('uploadToken')
    		        	},
    		        	success: function() {
    		        		deferred.resolve();
    		        	}
    		        });

    		        return deferred.promise();
    		    }

    		});

    	},

    	_makeFileHash: function(file) {
    		var start1 = 0;
    		var end1 = (file.size < 4096) ? file.size : 4096;
    		var promise1 = this.uploader.md5File(file, start1, end1);

    		var start2 = parseInt(file.size / 3);
    		var end2 = ((start2 + 4096) > file.size) ? file.size : start2 + 4096;
    		var promise2 = this.uploader.md5File(file, start2, end2);

    		var start3 = parseInt(file.size / 3 * 2);
    		var end3 = ((start3 + 4096) > file.size) ? file.size : start3 + 4096;
    		var promise3 = this.uploader.md5File(file, start3, end3);

    		var start4 = ((file.size - 4096) < 0) ? 0 : file.size - 4096;
    		var end4 = file.size;
    		var promise4 = this.uploader.md5File(file, start4, end4);

    		var deferred = WebUploader.Deferred();
    		WebUploader.when(promise1, promise2, promise3, promise4).done(function(hash1, hash2, hash3, hash4) {
    			var hash = hash1.slice(0, 8);
    			hash += hash2.slice(8, 16);
    			hash += hash3.slice(16, 24);
    			hash += hash4.slice(24, 32);
    			deferred.resolve(hash);

    		});
    		return deferred.promise();
    	},

    });

	var $el = $('#batch-uploader');
	var esuploader = new ESUploader({
		element: $el,
		initUrl: $el.data('initUrl'),
		finishUrl: $el.data('finishUrl')
	});


});