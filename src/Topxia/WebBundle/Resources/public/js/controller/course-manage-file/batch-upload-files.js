define(function(require, exports, module) {
	require('webuploader2');
	var store = require('store');

	(function (global) {
		var bit = /b$/;
		var si = {
			bits: ["B", "kb", "Mb", "Gb", "Tb", "Pb", "Eb", "Zb", "Yb"],
			bytes: ["B", "kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"]
		};

		var filesize = function (arg) {
			var descriptor = arguments[1] === undefined ? {} : arguments[1];

			var result = [];
			var skip = false;
			var val = 0;
			var e = undefined,
			    base = undefined,
			    bits = undefined,
			    ceil = undefined,
			    neg = undefined,
			    num = undefined,
			    output = undefined,
			    round = undefined,
			    unix = undefined,
			    spacer = undefined,
			    suffixes = undefined;

			if (isNaN(arg)) {
				throw new Error("Invalid arguments");
			}

			bits = descriptor.bits === true;
			unix = descriptor.unix === true;
			base = descriptor.base !== undefined ? descriptor.base : 2;
			round = descriptor.round !== undefined ? descriptor.round : unix ? 1 : 2;
			spacer = descriptor.spacer !== undefined ? descriptor.spacer : unix ? "" : " ";
			suffixes = descriptor.suffixes !== undefined ? descriptor.suffixes : {};
			output = descriptor.output !== undefined ? descriptor.output : "string";
			e = descriptor.exponent !== undefined ? descriptor.exponent : -1;
			num = Number(arg);
			neg = num < 0;
			ceil = base > 2 ? 1000 : 1024;

			// Flipping a negative number to determine the size
			if (neg) {
				num = -num;
			}

			// Zero is now a special case because bytes divide by 1
			if (num === 0) {
				result[0] = 0;

				if (unix) {
					result[1] = "";
				} else {
					result[1] = "B";
				}
			} else {
				// Determining the exponent
				if (e === -1 || isNaN(e)) {
					e = Math.floor(Math.log(num) / Math.log(ceil));
				}

				// Exceeding supported length, time to reduce & multiply
				if (e > 8) {
					val = val * (1000 * (e - 8));
					e = 8;
				}

				if (base === 2) {
					val = num / Math.pow(2, e * 10);
				} else {
					val = num / Math.pow(1000, e);
				}

				if (bits) {
					val = val * 8;

					if (val > ceil) {
						val = val / ceil;
						e++;
					}
				}

				result[0] = Number(val.toFixed(e > 0 ? round : 0));
				result[1] = si[bits ? "bits" : "bytes"][e];

				if (!skip && unix) {
					if (bits && bit.test(result[1])) {
						result[1] = result[1].toLowerCase();
					}

					result[1] = result[1].charAt(0);

					if (result[1] === "B") {
						result[0] = Math.floor(result[0]);
						result[1] = "";
					} else if (!bits && result[1] === "k") {
						result[1] = "K";
					}
				}
			}

			// Decorating a 'diff'
			if (neg) {
				result[0] = -result[0];
			}

			// Applying custom suffix
			result[1] = suffixes[result[1]] || result[1];

			// Returning Array, Object, or String (default)
			if (output === "array") {
				return result;
			}

			if (output === "exponent") {
				return e;
			}

			if (output === "object") {
				return { value: result[0], suffix: result[1] };
			}

			return result.join(spacer);
		};

		global.filesize = filesize;
	})(typeof global !== "undefined" ? global : window);


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