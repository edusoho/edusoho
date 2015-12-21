define(function(require, exports, module) {
	exports.run = function() {
    $btn = $('#submit');
    $btn.on('click',function(){
	    $.get($('#data').data('url'),"",function(data){
	    	console.log(data);
		  });
	  });
	}
});