define(function(require, exports, module) {

    require('jquery.sortable');

    var ThemeManage = require('./theme-manage');

    exports.run = function() {
        var $themeEditContent = $('#theme-edit-content');

        $("#iframepage").load(function(){
            var mainheight = $(this).contents().find("body").outerHeight();
            $(this).height(mainheight);
        });

        var themeManage = new ThemeManage({
            element: '#theme-edit-content',
            config: $.parseJSON($('#theme-config').html()),
            allConfig: $.parseJSON($('#theme-all-config').html()),
            currentIframe: $('#iframepage')
        });

        $('body').data('themeManage', themeManage);

        $themeEditContent.on("click", '.check-box', function(event){
            event.stopPropagation();
            themeManage.getElement().trigger('save_config');
        });

        $themeEditContent.on("change", '#topNavNum', function(event){
          if (!validateTopNavNum($(this))) {
            return;
          }

          themeManage.getElement().trigger('save_config');
          return false;
        }); 
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

});