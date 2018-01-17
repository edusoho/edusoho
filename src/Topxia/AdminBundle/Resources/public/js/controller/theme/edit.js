define(function(require, exports, module) {

    require('jquery.sortable');
    var ThemeManage = require('./theme-manage');
    exports.run = function() {     
        var themeManage = new ThemeManage({
          element: '#theme-edit-content',
          config: $.parseJSON($('#theme-config').html()),
          allConfig: $.parseJSON($('#theme-all-config').html()),
          currentIframe: $('#iframepage')
        });
        $('body').data('themeManage', themeManage);

        $("#iframepage").load(function(){
            var mainheight = $(this).contents().find("body").outerHeight();
            $(this).height(mainheight);
        });
    };

});