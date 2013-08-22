define(function(require, exports, module) {

  exports.run = function() {

      $('.nickname').find('.follow-btn').on('click', function(){
          var $this = $(this);
          $.post($this.data('url'), function(){
            $this.hide();
            $('.nickname').find('.unfollow-btn').show();
          });
        });


      $('.nickname').find('.unfollow-btn').on('click', function(){
        var $this = $(this);
        $.post($this.data('url'), function(){
          $this.hide();
          $('.nickname').find('.follow-btn').show();
        });
      });

      $textAbout = $('.personal-about').next('p');
      var originText = $textAbout.html();
      var originHeight=$textAbout.height();
      var heightAfterCut;

      if(originText.length > 250){
        $textAbout.html(originText.substring(0,250));
        heightAfterCut=$textAbout.height();
        $textAbout.parents('.panel')
          .append("<h5> <a class='action-personal-about pull-right' href='javascript:;'> 更多</a> </h5>");
      }

      $('.action-personal-about').on('click', function(){
        var currentHeight=$textAbout.height();
        if($textAbout.html().length > 250){
          $textAbout.height(heightAfterCut).html(originText.substring(0,250));
          $(this).html("更多");
        } else {
          $textAbout.height(originHeight).html(originText);
          $(this).html("合起");
        }        
      });

      
  }

});