define(function(require, exports, module) {

    exports.run = function() {
    	

    	$("#uploader").pluploadQueue({

			runtimes : 'html5,flash,silverlight,html4',
			url : '../upload.php',
			chunk_size : '1mb',
			unique_names : true,
			
			filters : {
				max_file_size : '10mb',
				mime_types: [
					{title : "Audio Files", extensions : "mp3,wma,mp2,wav,aiff,aif,m4a,ra,dss"},
		            {title : "Video files", extensions : "mov,mp4,wmv,rm"},
					{title : "Image files", extensions : "jpg,gif,png"},
					{title : "Zip files", extensions : "zip"}
				]
			},

			resize : {width : 320, height : 240, quality : 90},

			flash_swf_url : '../../js/Moxie.swf',
			silverlight_xap_url : '../../js/Moxie.xap'
		});

		$('#form').submit(function(e) {
		// Files in queue upload them first
			if ($('#uploader').plupload('getFiles').length > 0) {

				// When all files are uploaded submit form
				$('#uploader').on('complete', function() {
					$('#form')[0].submit();
				});

				$('#uploader').plupload('start');
			} else {
				alert("You must have at least one file in the queue.");
			}
			return false; // Keep the form from submitting

		});


    };

});