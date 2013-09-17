define(function(require, exports, module) {

    exports.run = function() {

        $("#init-system").on('click', function(){
           $(this).addClass("disabled").text("正在初始化系统...");   
        });

        $('#upload_mode').on('change', function(){
    		var storage = $('[name=upload_mode]:checked').val();
    		if(storage == 'local'){
	        	$('.cloud-storage').hide();
    		} else if (storage == 'cloud'){
    			$('.cloud-storage').show();
    		}

        });

    };

});