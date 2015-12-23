define(function(require, exports, module) {
	exports.run = function() {
    $btn = $('#submit');
    var markerId = $('#data').data('markerid');
    var questionId = $('#data').data('questionid');
    $btn.on('click',function(){
	    $.get($('#data').data('url'),{"markerId":markerId,"questionId":questionId},function(data){
	    	var player = window.BalloonPlayer;
	    	player.trigger('doNextQuestionMarker',data);
		  });
	  });
	  $("input[name='answer["+questionId+"]']").on('click',function(){
      if($(this).is(':checked')) {
      	$(this).attr("checked","checked").parent().siblings().find("input").removeAttr("checked"); 
      }
    });
	}
});