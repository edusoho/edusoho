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
          var iframe = $(window.frames["iframepage"].document)[0];
          var bHeight = iframe.body.scrollHeight;
          var dHeight = iframe.documentElement.scrollHeight;
          var height = Math.max(bHeight, dHeight);
          $(this).height(height);
        });
    };

});