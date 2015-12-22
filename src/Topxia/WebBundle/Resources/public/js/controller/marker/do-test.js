define(function(require, exports, module) {
	exports.run = function() {
    $btn = $('#submit');
    $btn.on('click',function(){
	    $.get($('#data').data('url'),"",function(data){
	    	var player = window.BalloonPlayer;
	    	player.trigger('doNextQuestionMarker',data);
		  });
	  });
	}
});