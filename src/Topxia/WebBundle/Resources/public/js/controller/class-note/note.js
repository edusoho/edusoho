define(function(require, exports, module) {
        
  exports.run = function() {
    var $table = $('#note-list');
    $table.find('.class-note-praise').hide();

    $table.find('.praiseShowVar').each(function(){
        if($(this).val()==null || $(this).val()==""){
            $(this).nextAll('.praise').eq(0).show();
            $(this).nextAll('.canclePraise').eq(0).hide();
        }else{
            $(this).nextAll('.praise').eq(0).hide();
            $(this).nextAll('.canclePraise').eq(0).show();
        }
    });

    $table.on('click', '.short-text', function() {
        var $short = $(this);
        $short.parent().next(".class-note-praise").show();
        $short.slideUp('fast').parents('.short-long-text').find('.long-text').slideDown('fast');
    	
    });

    $table.on('click', '.long-text', function() {
        var $long = $(this);
        $long.parent().next(".class-note-praise").hide();
        $long.slideUp('fast').parents('.short-long-text').find('.short-text').slideDown('fast');
    });

    $table.on('click', '.praise', function() {
    	var $self=$(this);
    	$.get($self.data().url, function(html){
            var $praiseUser=$self.parent().next('.class-note-praise-member').find('span');
            $praiseUser.html(getPraiseText(html));
            $self.hide();
    		$self.next('.canclePraise').show();
        });
    });

    $table.on('click', '.canclePraise', function() {
    	var $self=$(this);
    	$.get($self.data().url, function(html){
            var $praiseUser=$self.parent().next('.class-note-praise-member').find('span');
            $praiseUser.html(getPraiseText(html));
    		$self.hide();
    		$self.prev('.praise').show();
        });
    });

    function getPraiseText($praiseArray){
        var $praiseUserText="";
        if($praiseArray.length>4){
            for (var i = 0;i<4;i++) {
                $praiseUserText+=("<a href='/user/"+$praiseArray[i].userId+"'>"+$praiseArray[i].truename+"</a>、");
            }
            $praiseUserText=$praiseUserText.substring(0,$praiseUserText.length-1)+' 等'+$praiseArray.length+'人赞过';
        }else if($praiseArray.length>0){
            for (var i = 0;i<$praiseArray.length;i++) {
                $praiseUserText+=("<a href='/user/"+$praiseArray[i].userId+"'>"+$praiseArray[i].truename+'</a>、');
            }
            $praiseUserText=$praiseUserText.substring(0,$praiseUserText.length-1)+' 赞过';
        }else{
            return $praiseUserText;
        }
        return $praiseUserText;
    }
  };
    
});