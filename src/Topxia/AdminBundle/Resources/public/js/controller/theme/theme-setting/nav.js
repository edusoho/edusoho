define(function(require, exports, module) {
    exports.run = function() {
      var $themeEditContent = $('#theme-edit-content');
      $themeEditContent.on("change", '#topNavNum', function(event){
        if (!validateTopNavNum($(this))) {
          return;
        }
        var config = getNavigation();
        $themeEditContent.trigger('save_config', config);
        return false;
      }); 

      var getNavigation = function(){
        var topNum = $themeEditContent.find('#topNavNum').val();
        
        return {
          navigation: {
            topNavNum: topNum
          },
        };    
      };

      var validateTopNavNum = function($elment) {
        var value = $elment.val();
        if (value && (/(^[1-9]\d*$)/.test(value)) && value >= 1 && value <= 99) {
          $elment.parent().removeClass('has-error');
          $elment.next().addClass('hide');
          return true;
        } else {
          $elment.parent().addClass('has-error');
          $elment.next().removeClass('hide');
        }

        return false;
      }
    }
});