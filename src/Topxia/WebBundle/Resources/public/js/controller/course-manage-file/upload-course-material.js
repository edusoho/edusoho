define(function(require, exports, module) {

	require('jquery.plupload-queue-css');
    require('jquery.plupload-queue');
    require('plupload');

    exports.run = function() {
    	var uploadCourseMaterialAsOne = $("#upload-course-material-as-one").attr('value');

    	var uploader = $("#uploader").pluploadQueue({

			runtimes : 'html5,flash,silverlight,html4',
			url : uploadCourseMaterialAsOne,
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
			silverlight_xap_url : '/assets/libs/jquery-plugin/plupload-queue/2.0.0/Moxie.xap'
			
		}); 

    };

});