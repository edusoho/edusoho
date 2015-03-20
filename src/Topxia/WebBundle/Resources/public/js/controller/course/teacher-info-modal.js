define(function(require, exports, module) {

    exports.run = function() {
    	var $teacherCard = $('.teacher-profile-card');

        $teacherCard.find('.follow-btn').on('click', function(){
        	var $this = $(this);
        	$.post($this.data('url'), function(){
        		$this.hide();
        		$teacherCard.find('.unfollow-btn').show();
        	});
        });

        $teacherCard.find('.unfollow-btn').on('click', function(){
        	var $this = $(this);
        	$.post($this.data('url'), function(){
        		$this.hide();
        		$teacherCard.find('.follow-btn').show();
        	});
        });

        $('#message_to_teacher').on('click',function(){
            var $this = $(this);
            $.post($this.data('url'),function(res){
                $('#modal').html(res);
            })
        });

    }

});