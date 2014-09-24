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
            $likeUser.html(getLikeText(html));
            $self.hide();
    		$self.next('.cancleLike').show();
        });
    });

    $table.on('click', '.cancleLike', function() {
    	var $self=$(this);
    	$.get($self.data().url, function(html){
            var $likeUser=$self.parent().next('.class-note-like-member').find('span');
            $likeUser.html(getLikeText(html));
    		$self.hide();
    		$self.prev('.like').show();
        });
    });

    function getLikeText($likeArray){
        var $likeUserText="";
        if($likeArray.length>4){
            for (var i = 0;i<4;i++) {
                $likeUserText+=("<a href='/user/"+$likeArray[i].userId+"'>"+$likeArray[i].truename+"</a>、");
            }
            $likeUserText=$likeUserText.substring(0,$likeUserText.length-1)+' 等'+$likeArray.length+'人赞过';
        }else if($likeArray.length>0){
            for (var i = 0;i<$likeArray.length;i++) {
                $likeUserText+=("<a href='/user/"+$likeArray[i].userId+"'>"+$likeArray[i].truename+'</a>、');
            }
            $likeUserText=$likeUserText.substring(0,$likeUserText.length-1)+' 赞过';
        }else{
            return $likeUserText;
        }
        return $likeUserText;
    }
  };
    
});