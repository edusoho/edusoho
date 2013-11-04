define(function(require, exports, module) {

	require('jquery.plupload-queue-css');
    require('jquery.plupload-queue');
    require('plupload');

    exports.run = function() {
    	
    	function log() {
			var str = "";

			plupload.each(arguments, function(arg) {
				var row = "";

				if (typeof(arg) != "string") {
					plupload.each(arg, function(value, key) {
						// Convert items in File objects to human readable form
						if (arg instanceof plupload.File) {
							// Convert status to human readable
							switch (value) {
								case plupload.QUEUED:
									value = 'QUEUED';
									break;

								case plupload.UPLOADING:
									value = 'UPLOADING';
									break;

								case plupload.FAILED:
									value = 'FAILED';
									break;

								case plupload.DONE:
									value = 'DONE';
									break;
							}
						}

						if (typeof(value) != "function") {
							row += (row ? ', ' : '') + key + '=' + value;
						}
					});

					str += row + " ";
				} else { 
					str += arg + " ";
				}
			});
			console.log(str);
		}

    	var uploadCourseWareAsChunk = $("#upload-course-ware-as-chunk").attr('value');


    	var uploader = $("#uploader").pluploadQueue({

			runtimes : 'html5,flash,silverlight,html4',
			url : uploadCourseWareAsChunk,
			chunk_size : '1mb',
			max_file_size : '10mb',
			unique_names : true,
			filters : {
				max_file_size : '10mb',
				mime_types: [
					{title : "Audio Files", extensions : "mp3,wma,mp2,wav,aiff,aif,m4a,ra,dss"},
		  		 	{title : "Video files", extensions : "mov,mp4,wmv,rm,flv"},
					{title : "Image files", extensions : "jpg,gif,png"},
					{title : "Zip files", extensions : "zip"}
				]
			},

			resize : {width : 500, height : 500, quality : 90},
			flash_swf_url : '/assets/libs/jquery-plugin/plupload-queue/2.0.0/Moxie.swf',
			silverlight_xap_url : '/assets/libs/jquery-plugin/plupload-queue/2.0.0/Moxie.xap',
			preinit : {
				Init: function(up, info) {
					log('[Init]', 'Info:', info, 'Features:', up.features);
				},

				UploadFile: function(up, file) {
					log('[UploadFile]', file);

					// You can override settings before the file is uploaded
					// up.settings.url = 'upload.php?id=' + file.id;
					up.settings.url = uploadCourseWareAsChunk+'?id=' + file.id;
					// up.settings.multipart_params = {param1 : 'value1', param2 : 'value2'};
					up.settings.multipart_params = {id : file.id };
				}
			},

			init : {
				Refresh: function(up) {
					// Called when upload shim is moved
					log('[Refresh]');
				},

				StateChanged: function(up) {
					// Called when the state of the queue is changed
					log('[StateChanged]', up.state == plupload.STARTED ? "STARTED" : "STOPPED");
				},

				QueueChanged: function(up) {
					// Called when the files in queue are changed by adding/removing files
					log('[QueueChanged]');
				},

				UploadProgress: function(up, file) {
					// Called while a file is being uploaded
					log('[UploadProgress]', 'File:', file, "Total:", up.total);
				},

				FilesAdded: function(up, files) {
					// Callced when files are added to queue
					log('[FilesAdded]');

					plupload.each(files, function(file) {
						log('  File:', file);
					});
				},

				FilesRemoved: function(up, files) {
					// Called when files where removed from queue
					log('[FilesRemoved]');

					plupload.each(files, function(file) {
						log('  File:', file);
					});
				},

				FileUploaded: function(up, file, info) {
					// Called when a file has finished uploading
					log('[FileUploaded] File:', file, "Info:", info);
				},

				ChunkUploaded: function(up, file, info) {
					// Called when a file chunk has finished uploading
					log('[ChunkUploaded] File:', file, "Info:", info);
				},

				Error: function(up, args) {
					// Called when a error has occured
					log('[error] ', args);
				}
			}

		});

		$('form').submit(function(e) {
			var uploader = $('#uploader').pluploadQueue();

			if (uploader.total.uploaded == 0) {
				// Files in queue upload them first
				if (uploader.files.length > 0) {
					// When all files are uploaded submit form
					uploader.bind('UploadProgress', function() {
						if (uploader.total.uploaded == uploader.files.length)
							$('form').submit();
					});

					uploader.start();
				} else
					alert('你必须至少上传一个文件！');
				e.preventDefault();
			}
		});


    };

});