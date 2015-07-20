define(function(require, exports, module) {
	require('webuploader2');
	var store = require('store');
	var filesize = require('filesize');

	var $uploader = $('#batch-uploader');

	var defaults = {

	    dnd: $uploader.find('.balloon-uploader-body'),

	    // 不压缩image
	    resize: false,

	    // swf文件路径
	    swf: 'Uploader.swf',

	    // 文件接收服务端。
	    server: 'http://upload.escloud.com/chunks',

	    // 选择文件的按钮。可选。
	    // 内部根据当前运行是创建，可能是input元素，也可能是flash.
	    pick: $uploader.find('.file-pick-btn') ,
	    chunked: true,
	    chunkSize: 1024000,
	    chunkRetry: 2,
	    threads: 1,
	    formData: {

	    }
	};

	var continueChunkNum = 0;

	WebUploader.Uploader.register({
	    'before-send-file': 'preupload',
	    'before-send' : 'checkchunk',
	    'after-send-file': 'finishupload',
	}, {
	    preupload: function(file) {
	        console.log('before-send-file', file);

	        var deferred = WebUploader.Deferred();

	        uploader.md5File( file, 0, 10240 ).then(function(hash) {
	        	file.hash = hash;
	        	continueChunkNum = store.get('file_' + hash);
	        	var params = {
	        		fileName: file.name,
	        		fileSize: file.size,
	        		fileHash: hash,
	        		chunkSize: defaults.chunkSize,
	        		targetType: $uploader.data('target-type'),
	        		targetId: $uploader.data('target-id'),
	        		convertor: '',
	        		convertParams: {'qulity': 'low'}
	        	}

	        	$.support.cors = true; 

	        	$.post($uploader.data('initUrl'), params, function(response) {
	        		file.gid = response.globalId;
	        		$.post('http://upload.escloud.com/start', {file_gid: file.gid, file_size:file.size, file_name:file.name}, function() {
	        		    deferred.resolve();
	        		});

	        	}, 'json');

	        });

	        return deferred.promise();
	    },

	    checkchunk: function(block) {
	    	console.log('checkchunk', block);
	    	var deferred = WebUploader.Deferred();

	    	if (continueChunkNum && continueChunkNum > 0 && block.chunk < continueChunkNum) {
	    		console.log('秒传');
	    		deferred.reject();
	    	}

	    	deferred.resolve();

	    	return deferred.promise();
	    },

	    finishupload: function(file) {
	        console.log('finish-upload', file);
	        store.remove('file_' + file.hash);
	        var deferred = WebUploader.Deferred();
	        $.post('http://upload.escloud.com/finish', {file_gid:file.gid}, function() {
	            deferred.resolve();
	        });
	        return deferred.promise();
	    }

	});

	
	var uploader = WebUploader.create(defaults);

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
	    $.post($uploader.data('finishedUrl'), {fileId:file.gid}, function() {
	    	console.log('finished');

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
	});

	$('.balloon-uploader .start-upload-btn').on('click', function(){
	    uploader.upload();
	});

});