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

  }

});