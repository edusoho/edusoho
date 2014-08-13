define(function(require, exports, module) {
    require('countdown');

    exports.run = function() {
    	if($('.countdown').length){
            var startTime = $('.countdown').data('starttime');
            var endTime = $('.countdown').data('endtime');
            var now = new Date();
            var targetTime = '';
            if(now < new Date(startTime)){
                targetTime = startTime;
            }else{
            targetTime = endTime;
        }
        
        $('.countdown').downCount({
            date: targetTime,
            offset: 8
            });

         setInterval(function(){
            $('#countdown-div').css("display","block");
        }, 1000);

        }

    }
});