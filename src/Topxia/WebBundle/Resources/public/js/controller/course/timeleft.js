define(function(require, exports, module) {
    require('countdown');

    exports.run = function() {
    	if($('.countdown').length){
            $('.countdown').each(function(){
                //var startTime = $(this).data('starttime');
                var endTime = $(this).data('endtime');
                if(endTime){
                     var now = new Date();
                    var targetTime = '';
                   /* if(now < new Date(startTime)){
                        targetTime = startTime;
                    }else{*/
                    targetTime = endTime;
                  //  }
                
                    $(this).downCount({
                        date: targetTime,
                        offset: 8,
                        timestep: 100
                    });
                }
               

                /*setInterval(function(){
                    $('#countdown-div').css("display","block");
                }, 1000);
                */
            });
           
        }

    }
});