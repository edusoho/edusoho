define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require('jquery.form');

    exports.run = function() {
        	 if (window.history && window.history.pushState) {
                $(window).on('popstate', function () {
                var hashLocation = location.hash;
                var hashSplit = hashLocation.split("#!/");
                var hashName = hashSplit[1];
            if (hashName !== '') {
                var hash = window.location.hash;
              if (hash === '') {
                  // alert("您已经提交试卷！不能再次进入考试页面");
                  location.reload();
            }
          }
        });
        window.history.pushState('forward', null, './result');
      }
    };

});



















