define(function(require, exports, module) {
        
    exports.run = function() {
        var $table = $('#note-list');
        $table.find('.class-note-like').hide();

        $table.find('.likeShowVar').each(function(){
            if($(this).val()==null || $(this).val()==""){
                $(this).nextAll('.like').eq(0).show();
                $(this).nextAll('.cancleLike').eq(0).hide();
            }else{
                $(this).nextAll('.like').eq(0).hide();
                $(this).nextAll('.cancleLike').eq(0).show();
            }
        });

        $table.on('click', '.short-text', function() {
            var $short = $(this);
            $short.parent().next(".class-note-like").show();
            $short.slideUp('fast').parents('.short-long-text').find('.long-text').slideDown('fast');
        	
        });

        $table.on('click', '.long-text', function() {
            var $long = $(this);
            $long.parent().next(".class-note-like").hide();
            $long.slideUp('fast').parents('.short-long-text').find('.short-text').slideDown('fast');
        });

        $table.on('click', '.like', function() {
        	var $self=$(this);
        	$.get($self.data().url, function(html){
                var $likeUser=$self.parent().next('.class-note-like-member').find('span');
                $likeUser.html(html);
                $self.hide();
        		$self.next('.cancleLike').show();
            });
        });

        $table.on('click', '.cancleLike', function() {
        	var $self=$(this);
        	$.get($self.data().url, function(html){
                var $likeUser=$self.parent().next('.class-note-like-member').find('span');
                $likeUser.html(html);
        		$self.hide();
        		$self.prev('.like').show();
            });
        });
    };
    
});