define(function(require, exports, module) {
	exports.run = function() {
    $btn = $('#submit');
    $btn.on('click',function(){
    	var markerId = $('#data').data('markerid');
    	var questionId = $('#data').data('questionid');
	    $.get($('#data').data('url'),{"markerId":markerId,"questionId":questionId},function(data){
	    	//console.log(data);
	    	var player = window.BalloonPlayer;
	    	player.trigger('doNextQuestionMarker',data);
		  });
	  });
	}
});