define(function(require, exports, module) {

 var Notify = require('common/bootstrap-notify');
		require('jquery.plupload-queue-css');
    require('jquery.plupload-queue');
    require('plupload');

  exports.run = function() {
	
	var uploadCourseLessonAsOne = $("#upload-course-lesson-as-one").attr('value');


	var uploader = $("#uploader").pluploadQueue({

			runtimes : 'html5,flash,silverlight,html4',
			url : uploadCourseLessonAsOne,
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
					
				},

				UploadFile: function(up, file) {
					
					// You can override settings before the file is uploaded
					// up.settings.url = 'upload.php?id=' + file.id;
					up.settings.url = uploadCourseLessonAsOne+'?id=' + file.id;
					// up.settings.multipart_params = {param1 : 'value1', param2 : 'value2'};
					up.settings.multipart_params = {id : file.id };
				}
			},

			init : {
				Refresh: function(up) {
					// Called when upload shim is moved
					
				},

				StateChanged: function(up) {
					// Called when the state of the queue is changed
					
				},

				QueueChanged: function(up) {
					
				},

				UploadProgress: function(up, file) {
					
				},

				FilesAdded: function(up, files) {
					// Callced when files are added to queue

					plupload.each(files, function(file) {
					});
				},

				FilesRemoved: function(up, files) {

					plupload.each(files, function(file) {
						
					});
				},

				FileUploaded: function(up, file, info) {
				},

				Error: function(up, args) {
				}
			}

		});

		$('form').submit(function(e) {
			var uploader = $('#uploader').pluploadQueue();

		if (uploader.total.uploaded == 0) {
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